<?php
/**
 *
 * EPV :: The phpBB Forum Extension Pre Validator.
 *
 * @copyright (c) 2014 phpBB Limited <https://www.phpbb.com>
 * @license       GNU General Public License, version 2 (GPL-2.0)
 *
 */
namespace Phpbb\Epv\Tests\Tests;


use Phpbb\Epv\Files\FileInterface;
use Phpbb\Epv\Files\Type\LangFileInterface;
use Phpbb\Epv\Files\Type\PHPFileInterface;
use Phpbb\Epv\Output\Output;
use Phpbb\Epv\Output\OutputInterface;
use Phpbb\Epv\Tests\BaseTest;
use Phpbb\Epv\Tests\Exception\TestException;
use Phpbb\Epv\Tests\Type;
use PhpParser\Error;
use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\BinaryOp\Concat;
use PhpParser\Node\Expr\BooleanNot;
use PhpParser\Node\Expr\Eval_;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Print_;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Declare_;
use PhpParser\Node\Stmt\Trait_;
use PhpParser\Node\Stmt\Echo_;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Use_;
use PhpParser\ParserFactory;


class epv_test_validate_php_functions extends BaseTest
{
    /**
     * @var \PhpParser\Parser
     */
	private $parser;

	/** @var bool */
	private $in_phpbb = true;

	/**
	 * Array with deprecated/removed functions.
	 *
	 * Key: old function name (Which is removed/deprecated)
	 * Value: If available, new function name
	 * @var array
	 */
	private $deprecated = array(
		'gen_email_hash'            => 'phpbb_email_hash($email)',
		'cache_moderators'          => 'phpbb_cache_moderators($db, $cache, $auth)',
		'update_foes'               => 'phpbb_update_foes($db, $auth, $group_id, $user_id)',
		'get_user_avatar'           => 'phpbb_get_avatar($row, $alt, $ignore_config)',
		'phpbb_hash'                => '$passwords_manager->hash($password)',
		'phpbb_check_hash'          => '$passwords_manager->check($password, $hash)',
		'phpbb_clean_path'          => '$phpbb_path_helper->clean_path($path)',
		'set_config'                => '$config->set($key, $value, $cache = true)',
		'request_var'               => '$request->variable()',
		'set_config_count'          => '$config->increment()',
		'tz_select'                 => 'phpbb_timezone_select($user, $default, $truncate)',
		'add_log'                   => '$phpbb_log->add()',

		'set_var'                   => '$type_cast_helper->set_var()',
		'get_tables'                => '$db_tools->sql_list_tables()',

		// Removed, Not deprecated
		'topic_generate_pagination' => 'phpbb_generate_template_pagination($template, $base_url, $block_var_name, $num_items, $per_page, $start_item = 1, $reverse_count = false, $ignore_on_page = false)',
		'generate_pagination'       => 'phpbb_generate_template_pagination($template, $base_url, $block_var_name, $num_items, $per_page, $start_item = 1, $reverse_count = false, $ignore_on_page = false)',
		'on_page'                   => 'phpbb_on_page($template, $user, $num_items, $per_page, $start)',
		'remove_comments'           => 'phpbb_remove_comments($input)',
		'remove_remarks'            => 'phpbb_remove_comments($input)',
	);

	/**
	 * List of dbal functions that should not be called.
	 * @var array
	 */
	private $dbal = array(
		'mysql_',
		'mysqli_',
		'oci_',
		'sqlite_',
		'pg_',
		'mssql_',
		'odbc_',
		'sqlsrv_',
		'ibase_',
		'db2_',
	);

	/**
	 * @param bool            $debug if debug is enabled
	 * @param OutputInterface $output
	 * @param string          $basedir
	 * @param string          $namespace
	 * @param boolean         $titania
	 * @param string          $opendir
	 */
	public function __construct($debug, OutputInterface $output, $basedir, $namespace, $titania, $opendir)
	{
		parent::__construct($debug, $output, $basedir, $namespace, $titania, $opendir);

		$this->fileTypeFull   = Type::TYPE_PHP;
		$this->parser         = (new ParserFactory)->create(ParserFactory::PREFER_PHP7);
	}

	/**
	 * @param FileInterface $file
	 *
	 * @throws \Phpbb\Epv\Tests\Exception\TestException
	 */
	public function validateFile(FileInterface $file)
	{
		if (!$file instanceof PHPFileInterface)
		{
			throw new TestException('This test expects a php type, but found something else.');
		}
		$this->validate($file);
	}

	/**
	 * Do the actual validation of the service file.
	 *
	 * @param PHPFileInterface $file
	 */
	private function validate(PHPFileInterface $file)
	{
		$this->output->writelnIfDebug('Attempting to parse file: ' . $file->getSaveFilename());

		$this->file     = $file;
		$this->in_phpbb = false;

		try
		{
			$stmt = $this->parser->parse($file->getFile());

			if ($file instanceof LangFileInterface)
			{
				// Language files are just 1 array with data.
				// Do a separate test.
				$this->parseLangNodes($stmt);
			}
			else
			{
				$this->parseNodes($stmt);
			}

			if (!$this->in_phpbb)
			{
				$this->checkInPhpBB($stmt);
			}
		}
		catch (Error $e) // Catch PhpParser error.
		{
			$this->output->addMessage(Output::FATAL, 'PHP parse error in file ' . $file->getSaveFilename() . '. Message: ' . $e->getMessage());
		}
	}

	/**
	 * Check if the missing IN_PHPBB is a valid one
	 *
	 * @param array $stmt Statements in this file.
	 */
	private function checkInPhpBB(array $stmt)
	{
		$ok = true;

		foreach ($stmt as $key => $statement)
		{
			if ($statement instanceof Declare_)
			{
				unset($stmt[$key]);
			}
		}

		// Re-index from 0
		$stmt = array_values($stmt);

		// Lets see if there is just a namespace + class
		if (count($stmt) == 1 && $stmt[0] instanceof Namespace_)
		{
			foreach ($stmt[0]->stmts as $st)
			{
				if ($st instanceof Class_ || $st instanceof Interface_ || $st instanceof Use_ || $st instanceof Declare_ || $st instanceof Trait_)
				{ // Statement is a class, interface, trait or a Use classname.
					continue;
				}
				$ok = false;
			}
		}
		else
		{
			// First statement is not a namespace.
			// We require a IN_PHPBB in all non namespaced files.
			$ok = false;
		}

		if ($this->isTest())
		{
			// We skip tests.
			$this->output->writelnIfDebug(sprintf('Skipped %s because of test file.', $this->file->getSaveFilename()));
			$ok = true;
		}

		// Override $ok if we are lang file
		if ($this->file instanceof LangFileInterface)
		{
			$ok = false;
		}

		if (!$ok)
		{
			// IN_PHPBB is not defined, and we don't have a exception on the rule.
			// Add a warning.
			$this->addMessage(Output::WARNING, 'IN_PHPBB is not defined');
		}
		else
		{
			$this->output->writelnIfDebug(sprintf('Did not find IN_PHPBB, but php file %s contains a namespace with just classes, interfaces, traits or is a test file.', $this->file->getSaveFilename()));
		}
	}


	/**
	 * Validate the structure of a php file.
	 *
	 * @param array $nodes
	 *
	 */
	private function parseNodes(array $nodes)
	{
		if (!($nodes[0] instanceof Namespace_))
		{
			foreach ($nodes as $node)
			{
				// Check if there is a class, interface or trait, there should be a namespace.
				if ($node instanceof Class_ || $node instanceof Interface_ || $node instanceof Trait_)
				{
					$this->addMessage($this->isTest() ? Output::NOTICE : Output::ERROR, 'All files with a class, interface or trait should have a namespace');
					break;
				}
			}

			$this->parseNode($nodes);
		}
		else
		{
			$this->parseNode($nodes[0]->stmts);

			if (count($nodes) > 1)
			{
				$this->addMessage(Output::WARNING, 'Besides the namespace, there should be no other statements');
			}
		}
	}

	/**
	 * Run validations on a Array of nodes. If a key contains a object or array, it will recursively call
	 * parseNode on these objects or arrays.
	 *
	 * Because the structure of a php is dynamically, and we (of course) don't want to run this twice to discover the number
	 * of tests (Due to slowness), we dynamically increase the maximum progress. In a perfect world, we would do a testrun
	 * first, and after that the real test.
	 *
	 * @param array $nodes
	 */
	private function parseNode(array $nodes)
	{
		foreach ($nodes as $node)
		{
			if ($node instanceof If_ && !$this->in_phpbb)
			{
				$this->checkInDefined($node);
				if ($this->in_phpbb)
				{
					// IN_PHPBB was found, we continue.
					// Do not remove this continue, because exit is required within IN_PHPBB,
					// And if we will continue, it will add warnings about using exit.
					// checkInDefined will make sure there are no other nodes within the check,
					// and if there are any nodes it will call parseNode on these statements.
					continue;
				}
			}

			// Because the array can contain more as just Nodes, we check for that here.
			if ($node instanceof Node)
			{
				$this->validateFunctionNames($node);
				$this->validateExit($node);
				$this->validatePrint($node);
				$this->validateMethodCalls($node);
			}

			if (is_array($node) || is_object($node))
			{
				foreach ($node as $nr)
				{
					if (is_array($nr))
					{
						$this->parseNode($nr);
					}
					else
					{
						$this->parseNode(array($nr));
					}
				}
			}
		}
	}

    /**
     * Check if the current node checks for IN_PHPBB, and
     * exits if it isn't defined.
     *
     * If IN_PHPBB is found, but there is no exit as first statement, it will not set IN_PHPBB, but will add a notice
     * instead for the user.  The other nodes will be send back to parseNode.
     *
     * @param \PhpParser\Node\Stmt\If_ $node if node that checks possible for IN_PHPBB
     */
	private function checkInDefined(If_ $node)
	{
		$cond = $node->cond;

		if ($cond instanceof BooleanNot && $cond->expr instanceof FuncCall && $cond->expr->name->getFirst() === 'defined' && $cond->expr->args[0]->value->value === 'IN_PHPBB')
		{

			if ($node->stmts[0]->expr instanceof Node\Expr\Exit_)
			{
				// Found IN_PHPBB
				$this->in_phpbb = true;
			}
			else
			{
				// Found IN_PHPBB, but it didn't exists?
				// We dont set $this->in_phpbb, so parseNode continue running on this node.
				// Also include a notice.
				$this->addMessage(Output::NOTICE, 'IN_PHPBB check should exit if it is not defined');
			}
			if (count($node->stmts) > 1)
			{
				$this->addMessage(Output::WARNING, 'There should be no statements other than exit in the IN_PHPBB check');
				unset($node->stmts[0]);
				$this->parseNode($node->stmts);
			}
		}
	}

    /**
     * Do certain validations on function names.
     *
     * @param Node $node Node to validate
     */
	private function validateFunctionNames(Node $node)
	{
		$name = null;
		if ($node instanceof FuncCall)
		{
			$name = $this->getMethodName($node);
		}
		else if (isset($node->expr) && $node->expr instanceof FuncCall && $node->expr->name instanceof Name)
		{
			$name = $node->expr->name->getFirst();
		}

		if ($name !== null)
		{
			$this->validateDbal($name, $node);
			$this->validateDeprecated($name, $node);
			$this->validateFunctions($name, $node);
		}
        $this->validateEval($node);
	}

	/**
	 * Validate method calls to classes.
	 * @param Node $node Node to validate
	 */
	private function validateMethodCalls(Node $node) {
		$name = null;
		if ($node instanceof Node\Expr\MethodCall)
		{
            $name = $this->getMethodName($node);
        }
		else if (
			isset($node->expr)
			&& $node->expr instanceof Node\Expr\MethodCall
			&& method_exists($node->expr->name, 'toString')
			&& !($node->expr->name instanceof Variable)
			&& !($node->expr->name instanceof PropertyFetch)
			&& !($node->expr->name instanceof Concat)
			&& !($node->expr->name instanceof Node\Scalar\Encapsed)
			&& !($node->expr->name instanceof Node\Expr\Ternary)
		)
		{
			$name = $node->expr->name->toString();
		}

		if ($name !== null)
		{
			$this->validateEnableGlobals($name, $node);
		}
	}

    /**
     * @param FuncCall|Expr\MethodCall $node
     * @return null|string
     */
    private function getMethodName(Node $node)
    {
        if ($node->name instanceof Variable || $node->name instanceof PropertyFetch || $node->name instanceof ArrayDimFetch)
        {
            return null; // This is a variable. We are going to ignore this. We do not want to track variable contents
        }
        else if ($node->name instanceof Concat)
        {
            // Only test if both are a string
            // This mean that if a user works around this test he can do so, but otherwise we will
            // need to parse variables and stuff.
            if ($node->name->left instanceof String_ && $node->name->right instanceof String_)
            {
                return $node->name->left->value . $node->name->right->value;
            }
        }
		else if ($node->name instanceof Node\Scalar\Encapsed)
		{
			$encapsed = '';
			foreach ($node->name->parts as $part)
			{
				if ($part instanceof Node\Scalar\EncapsedStringPart)
				{
					$encapsed .= $part->value;
				}
				else if ($part instanceof Variable)
				{
					$encapsed .= $part->name;
				}
				else if ($part instanceof PropertyFetch)
				{
					$encapsed .= $part->name->name;
				}
				else if ($part instanceof ArrayDimFetch)
				{
					$encapsed .= $part->var->name;
				}
				else
				{
					$encapsed .= $part->toString();
				}
			}
			return $encapsed ?: null;
		}
        else if ($node->name instanceof Node\Identifier)
        {
			return $node->name->name;
        }
		else if ($node->name instanceof Node\Name)
		{
			return $node->name->getFirst();
		}
		else
		{
			return $node->name->toString();
		}
        return null;
    }

    /**
     * Validate the use of enable_globals.
     *
     * @param $name
     * @param Node $node
     */
	private function validateEnableGlobals($name, Node $node)
	{
		if ($name == 'enable_super_globals')
		{
			$this->addMessage(Output::FATAL, sprintf('The use of enable_super_globals() is not allowed for security reasons on line %s', $node->getAttribute('startLine')));
		}
	}

	private function validateEval(Node $node)
    {
        if ($node instanceof Eval_)
        {
            $this->addMessage(Output::FATAL, sprintf('The use of eval() is not allowed for security reasons on line %s', $node->getAttribute('startLine')));
        }
    }

	/**
	 * Validate the use of deprecated functions.
	 *
	 * @param                 $name
	 * @param Node $node
	 */
	private function validateDeprecated($name, Node $node)
	{
		foreach ($this->deprecated as $depName => $dep)
		{
			if ($name == $depName)
			{
				$useInstead = '';

				if ($this->deprecated[$name])
				{
					$useInstead = sprintf(', you can use %s instead', $this->deprecated[$name]);
				}

				$this->addMessage(Output::WARNING, sprintf('Found a deprecated or removed function call to %s on line %s%s', $name, $node->getAttribute('startLine'), $useInstead));

				return;
			}
		}
	}

	/**
	 * Validate the use of non dbal names.
	 *
	 * @param string          $name function name
	 * @param Node $node
	 */
	private function validateDbal($name, Node $node)
	{
		foreach ($this->dbal as $dbal)
		{
			$length = strlen($dbal);

			if (strlen($name) < $length)
			{
				continue;
			}
			$call = substr($name, 0, $length);

			if ($call == $dbal)
			{
				$this->addMessage(Output::ERROR, sprintf('Found a disallowed call to %s on line %s. Please use the DBAL instead.', $name, $node->getAttribute('startLine')));

				return;
			}
		}
	}

	/**
	 * Validate if a node uses exit.
	 *
	 * @param Node $node Node to validate
	 */
	private function validateExit(Node $node)
	{
		if ($node instanceof Node\Expr\Exit_)
		{
			$this->addMessage(Output::WARNING, sprintf('Using exit on line %s', $node->getAttribute("startLine")));
		}
	}

	/**
	 * Validate if a node uses print or echo.
	 *
	 * @param Node $node Node to validate
	 */
	private function validatePrint(Node $node)
	{
		if ($node instanceof Print_ || $node instanceof Echo_)
		{
			$this->addMessage(Output::ERROR, sprintf('The template system should be used instead of echo or print on line %s', $node->getAttribute('startLine')));
		}
	}

	/**
	 * Validate if a node uses certain functions that should not be used within phpBB.
	 *
	 * @param                 $name
	 * @param Node $node Node to validate
	 */
	private function validateFunctions($name, Node $node)
	{
		$warn_array = array(
			'exec'             => Output::ERROR,
			'shell_exec'	   => Output::ERROR,
			'system'           => Output::ERROR,
			'passthru'         => Output::ERROR,
			'getenv'           => Output::ERROR,
			'die'              => Output::ERROR,
			'addslashes'       => Output::ERROR,
			'stripslashes'     => Output::ERROR,
			'htmlspecialchars' => Output::ERROR,
			'include_once'     => Output::WARNING,
			'require_once'     => Output::WARNING,
			'md5'              => Output::NOTICE,
			'sha1'             => Output::NOTICE,
			'var_dump'         => Output::ERROR,
			'print_r'          => Output::ERROR,
			'printf'           => Output::ERROR,
			'unserialize'      => Output::ERROR,
		);

		foreach ($warn_array as $err => $level)
		{
			if ($name == $err)
			{
				$this->addMessage($level, sprintf('Using %s on line %s', $err, $node->getAttribute('startLine')));

				return;
			}
		}
	}

	/**
	 * Validate a language file.
	 *
	 * @param array $node
	 */
	private function parseLangNodes(array $node)
	{
		$this->parseNode($node);
	}

	/**
	 * Add a new Message to Messages.
	 * The filename is automatically added.
	 *
	 * @param $type
	 * @param $message
	 */
	private function addMessage($type, $message)
	{
		$this->output->addMessage($type, sprintf("%s in %s", $message, $this->file->getSaveFilename()));
	}

	/**
	 *
	 * @return String
	 */
	public function testName()
	{
		return 'Validate php structure and deprecated functions';
	}

}
