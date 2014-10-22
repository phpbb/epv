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
use PHPParser_Error;
use PHPParser_Lexer_Emulative;
use PHPParser_Node;
use PHPParser_Node_Expr_Exit;
use PHPParser_Node_Expr_FuncCall;
use PHPParser_Node_Expr_Print;
use PHPParser_Node_Expr_Variable;
use PHPParser_Node_Stmt_Class;
use PHPParser_Node_Stmt_Echo;
use PHPParser_Node_Stmt_If;
use PHPParser_Node_Stmt_Interface;
use PHPParser_Node_Stmt_Namespace;
use PHPParser_Node_Stmt_Use;
use PHPParser_Parser;


class epv_test_validate_php_functions extends BaseTest
{
	/** @var \PHPParser_Parser */
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
	 */
	public function __construct($debug, OutputInterface $output, $basedir, $namespace, $titania)
	{
		parent::__construct($debug, $output, $basedir, $namespace, $titania);

		$this->fileTypeFull   = Type::TYPE_PHP;
		$this->parser         = new PHPParser_Parser(new PHPParser_Lexer_Emulative());
		$this->totalFileTests = 2;
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
		catch (PHPParser_Error $e) // Catch PhpParser error.
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
		// Lets see if there is just a namespace + class
		if (sizeof($stmt) == 1 && $stmt[0] instanceof PHPParser_Node_Stmt_Namespace)
		{
			foreach ($stmt[0]->stmts as $st)
			{
				if ($st instanceof PHPParser_Node_Stmt_Class || $st instanceof PHPParser_Node_Stmt_Interface || $st instanceof PHPParser_Node_Stmt_Use)
				{ // Statement is a class, interface or a Use classname.
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
			$this->output->writelnIfDebug(sprintf('Did not find IN_PHPBB, but php file contains a namespace with just classes or is a test file.', $this->file->getSaveFilename()));
		}
	}


	/**
	 * Validate the structure of a php file.
	 *
	 * @param array $nodes
	 *
	 * @internal param array $node
	 */
	private function parseNodes(array $nodes)
	{
		if (!($nodes[0] instanceof PHPParser_Node_Stmt_Namespace))
		{
			$err = false;
			foreach ($nodes as $node)
			{
				// Check if there is a class.
				// If there is a class, there should be a namespace.
				if ($node instanceof PHPParser_Node_Stmt_Class || $node instanceof PHPParser_Node_Stmt_Interface)
				{
					$this->addMessage($this->isTest() ? Output::NOTICE : Output::ERROR, 'All files with a class or an interface should have a namespace');
					$err = true;
					break;
				}
			}

			$this->parseNode($nodes);

			return;
		}
		else
		{
			$this->parseNode($nodes[0]->stmts);

			if (sizeof($nodes) > 1)
			{
				$this->addMessage(Output::WARNING, 'Besides the namespace, there should be no other statements');
			}
		}
	}

	/**
	 * Run validations on a Array of nodes. If a key contains a object or array, it will recursivly call
	 * parseNode on these objects or arrays.
	 *
	 * Because the structure of a php is dynamicly, and we (ofcourse) don't want to run this twice to discover the number
	 * of tests (Due to slowness), we dynamicly increase the maximum progress. In a perfect world, we would do a testrun
	 * first, and after that the real test.
	 *
	 * @param array $nodes
	 */
	private function parseNode(array $nodes)
	{
		foreach ($nodes as $node)
		{
			if ($node instanceof PHPParser_Node_Stmt_If && !$this->in_phpbb)
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
			if ($node instanceof PHPParser_Node)
			{
				$this->validateFunctionNames($node);
				$this->validateExit($node);
				$this->validatePrint($node);
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
	 * exits if it isnt defined.
	 *
	 * If IN_PHPBB is found, but there is no exit as first statement, it will not set IN_PHPBB, but will add a notice
	 * instead for the user.  The other nodes will be send back to parseNode.
	 *
	 * @param \PHPParser_Node_Stmt_If $node if node that checks possible for IN_PHPBB
	 */
	private function checkInDefined(PHPParser_Node_Stmt_If $node)
	{
		$cond = $node->cond;

		if ($cond instanceof \PHPParser_Node_Expr_BooleanNot && $cond->expr instanceof PHPParser_Node_Expr_FuncCall && $cond->expr->name == 'defined' && $cond->expr->args[0]->value->value == 'IN_PHPBB')
		{

			if ($node->stmts[0] instanceof PHPParser_Node_Expr_Exit)
			{
				// Found IN_PHPBB
				$this->in_phpbb = true;
			}
			else
			{
				// Found IN_PHPBB, but it didn't exists?
				// We dont set $this->in_phpbb, so parseNode continue running on this node.
				// Also include a notice.
				$this->addMessage(Output::NOTICE, 'IN_PHPBB check should exit if it is not defined.');
			}
			if (sizeof($node->stmts) > 1)
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
	 * @param \PHPParser_Node $node Node to validate
	 */
	private function validateFunctionNames(PHPParser_Node $node)
	{
		$name = null;
		if ($node instanceof PHPParser_Node_Expr_FuncCall)
		{
			if ($node->name instanceof PHPParser_Node_Expr_Variable)
			{
				// If function name is a variable.
				$name = (string)$node->name->name;
			}
			else
			{
				$name = (string)$node->name;
			}
		}
		else if (isset($node->expr) && $node->expr instanceof PHPParser_Node_Expr_FuncCall)
		{
			$name = (string)$node->expr->name->subNodes[0];
		}

		if ($name != null)
		{
			$this->validateDbal($name, $node);
			$this->validateDeprecated($name, $node);
			$this->validateFunctions($name, $node);
		}
	}

	/**
	 * Valdiate the use of deprecated functions.
	 *
	 * @param                 $name
	 * @param \PHPParser_Node $node
	 */
	private function validateDeprecated($name, PHPParser_Node $node)
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
	 * @param \PHPParser_Node $node
	 */
	private function validateDbal($name, PHPParser_Node $node)
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
	 * @param \PHPParser_Node $node Node to validate
	 */
	private function validateExit(PHPParser_Node $node)
	{
		if ($node instanceof PHPParser_Node_Expr_Exit)
		{
			$this->addMessage(Output::WARNING, sprintf('Using exit on line %s', $node->getAttribute("startLine")));
		}
	}

	/**
	 * Validate if a node uses print or echo.
	 *
	 * @param PHPParser_Node $node Node to validate
	 */
	private function validatePrint(PHPParser_Node $node)
	{
		if ($node instanceof PHPParser_Node_Expr_Print || $node instanceof PHPParser_Node_Stmt_Echo)
		{
			$this->addMessage(Output::ERROR, sprintf('The template system should be used instead of echo or print on line %s', $node->getAttribute('startLine')));
		}
	}

	/**
	 * Validate if a node uses certain functions that should not be used within phpBB.
	 *
	 * @param                 $name
	 * @param \PHPParser_Node $node Node to validate
	 */
	private function validateFunctions($name, PHPParser_Node $node)
	{
		$warn_array = array(
			'eval'             => Output::ERROR,
			'exec'             => Output::ERROR,
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
