<?php
/**
 *
 * @package EPV
 * @copyright (c) 2014 phpBB Group
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */
namespace epv\Tests\Tests;


use epv\Files\FileInterface;
use epv\Files\Type\LangFileInterface;
use epv\Files\Type\PHPFileInterface;
use epv\Output\Output;
use epv\Output\OutputInterface;
use epv\Tests\BaseTest;
use epv\Tests\Exception\TestException;
use PhpParser\Error;
use PhpParser\Lexer\Emulative;
use PhpParser\Node\Expr\BooleanNot;
use PhpParser\Node\Expr\Exit_;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Print_;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Echo_;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Use_;
use PhpParser\Node;
use PhpParser\Parser;

class epv_test_validate_php_functions extends BaseTest
{
    /** @var  \PhpParser\Parser */
    private $parser;

    /** @var bool  */
    private $in_phpbb = true;

    /**
     * Array with deprecated/removed functions.
     *
     * Key: old function name (Which is removed/deprecated)
     * Value: If available, new function name
     * @var array
     */
    private $deprecated = array(
        'gen_email_hash' => 'phpbb_email_hash($email)',
        'cache_moderators' => 'phpbb_cache_moderators($db, $cache, $auth)',
        'update_foes' => 'phpbb_update_foes($db, $auth, $group_id, $user_id)',
        'get_user_avatar' => 'phpbb_get_avatar($row, $alt, $ignore_config)',
        'phpbb_hash' => '$passwords_manager->hash($password)',
        'phpbb_check_hash' => '$passwords_manager->check($password, $hash)',
        'phpbb_clean_path' => '$phpbb_path_helper->clean_path($path)',
        'set_config' => '$config->set($key, $value, $cache = true)',
        'request_var' => '$request->variable()',
        'set_config_count' => '$config->increment()',
        'tz_select' => 'phpbb_timezone_select($user, $default, $truncate)',
        'add_log' => '$phpbb_log->add()',

        'set_var' => '$type_cast_helper->set_var()',
        'get_tables' => '$db_tools->sql_list_tables()',

            // Removed, Not deprecated
        'topic_generate_pagination' => 'phpbb_generate_template_pagination($template, $base_url, $block_var_name, $num_items, $per_page, $start_item = 1, $reverse_count = false, $ignore_on_page = false)',
        'generate_pagination' => 'phpbb_generate_template_pagination($template, $base_url, $block_var_name, $num_items, $per_page, $start_item = 1, $reverse_count = false, $ignore_on_page = false)',
        'on_page' => 'phpbb_on_page($template, $user, $num_items, $per_page, $start)',
        'remove_comments' => 'phpbb_remove_comments($input)',
        'remove_remarks' => 'phpbb_remove_comments($input)',
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
     * @param bool $debug if debug is enabled
     * @param OutputInterface $output
     * @param $basedir
     */
    public function __construct($debug, OutputInterface $output, $basedir)
    {
        parent::__construct($debug, $output, $basedir);

        $this->fileTypeFull = Type::TYPE_PHP;
        $this->parser = new Parser(new Emulative());
        $this->totalFileTests = 2;
    }

    /**
     * @param FileInterface $file
     * @throws \epv\Tests\Exception\TestException
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
     * @param PHPFileInterface $file
     */
    private function validate(PHPFileInterface $file)
    {
        $this->output->writelnIfDebug('Attempting to parse file: ' . $file->getFilename());

        $this->file = $file;
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
            else
            {
                $this->output->printErrorLevel();
            }
        }
        catch (Error $e) // Catch PhpParser error.
        {
            $this->output->addMessage(Output::FATAL, 'PHP parse error in file ' . $file->getFilename() . '. Message: ' . $e->getMessage());
        }
    }

    /**
     * Check if the missing IN_PHPBB is a valid one
     * @param array $stmt Statements in this file.
     */
    private function checkInPhpBB(array $stmt)
    {
        $ok = true;
        // Lets see if there is just a namespace + class
        if (sizeof($stmt) == 1 && $stmt[0] instanceof Namespace_)
        {
            foreach ($stmt[0]->stmts as $st)
            {
                if ($st instanceof Class_ || $st instanceof Interface_ || $st instanceof Use_)
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
            $this->output->writelnIfDebug(sprintf('Skipped %s because of test file.', $this->file->getFilename()));
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
            $this->output->printErrorLevel();
            $this->output->writelnIfDebug(sprintf('Did not find IN_PHPBB, but php file contains a namespace with just classes or is a test file.', $this->file->getFilename()));
        }
    }


    /**
     * Validate the structure of a php file.
     * @param array $nodes
     * @internal param array $node
     */
    private function parseNodes(array $nodes)
    {
        if (!($nodes[0] instanceof Namespace_))
        {
            $err = false;
            foreach ($nodes as $node)
            {
                // Check if there is a class.
                // If there is a class, there should be a namespace.
                if ($node instanceof Class_ || $node instanceof Interface_)
                {
                    $this->addMessage($this->isTest() ? Output::NOTICE : Output::ERROR, 'All files with a class or an interface should have a namespace');
                    $err = true;
                    break;
                }
            }

            if (!$err)
            {
                $this->output->printErrorLevel();
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
            else
            {
                $this->output->printErrorLevel();
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
     * @param If_ $node if node that checks possible for IN_PHPBB
     */
    private function checkInDefined(If_ $node)
    {
        $cond = $node->cond;

        if ($cond instanceof BooleanNot && $cond->expr instanceof FuncCall && $cond->expr->name == 'defined' && $cond->expr->args[0]->value->value == 'IN_PHPBB')
        {
            $this->output->inMaxPogress(2);

            if ($node->stmts[0] instanceof Exit_)
            {
                // Found IN_PHPBB
                $this->in_phpbb = true;
                $this->output->printErrorLevel();
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
            else
            {
                $this->output->printErrorLevel();
            }
        }
    }

    /**
     * Do certian validations on function names.
     *
     * @param Node $node Node to validate
     */
    private function validateFunctionNames(Node $node)
    {
        $name = null;
        if ($node instanceof FuncCall)
        {
            $name = (string)$node->name;
        }
        else if (isset($node->expr) && $node->expr instanceof FuncCall)
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
     * @param $name
     * @param \PhpParser\Node $node
     */
    private function validateDeprecated($name, Node $node)
    {
        $this->output->inMaxPogress(1);

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
        $this->output->printErrorLevel();
    }

    /**
     * Validate the use of non dbal names.
     * @param string $name function name
     * @param \PhpParser\Node $node
     */
    private function validateDbal($name, Node $node)
    {
        $this->output->inMaxPogress(1);

        foreach ($this->dbal as $dbal)
        {
            $length = strlen($dbal);

            if (strlen ($name) < $length)
            {
                continue;
            }
            $call = substr($name, 0, $length);

            if ($call == $dbal)
            {
                $this->addMessage(Output::FATAL, sprintf('Found a disallowed call to %s on line %s. Please use the DBAL instead.', $name, $node->getAttribute('startLine')));
                return;
            }
        }
        $this->output->printErrorLevel();
    }

    /**
     * Validate if a node uses exit.
     *
     * @param Node $node Node to validate
     */
    private function validateExit(Node $node)
    {
        $this->output->inMaxPogress(1);
        if ($node instanceof Exit_)
        {
            $this->addMessage(Output::WARNING, sprintf('Using exit on line %s', $node->getAttribute("startLine")));
        }
        else
        {
            $this->output->printErrorLevel();
        }
    }

    /**
     * Validate if a node uses print or echo.
     *
     * @param Node $node Node to validate
     */
    private function validatePrint(Node $node)
    {
        $this->output->inMaxPogress(1);
        if ($node instanceof Print_ || $node instanceof Echo_)
        {
            $this->addMessage(Output::ERROR, sprintf('The template system should be used instead of echo or print on line %s', $node->getAttribute('startLine')));
        }
        else
        {
            $this->output->printErrorLevel();
        }
    }

    /**
     * Validate if a node uses certian functions that should not be used within phpBB.
     *
     * @param $name
     * @param Node $node Node to validate
     */
    private function validateFunctions($name, Node $node)
    {
        $warn_array = array(
            'eval' 			=> Output::ERROR,
            'exec' 			=> Output::ERROR,
            'system' 		=> Output::ERROR,
            'passthru' 		=> Output::ERROR,
            'getenv'		=> Output::ERROR,
            'die'			=> Output::ERROR,
            'addslashes'	=> Output::ERROR,
            'stripslashes'	=> Output::ERROR,
            'htmlspecialchars'	=> Output::ERROR,
            'include_once'	=> Output::WARNING,
            'require_once' 	=> Output::WARNING,
            'md5'			=> Output::WARNING,
            'sha1'			=> Output::WARNING,
            'var_dump'      => Output::ERROR,
            'print_r'       => Output::ERROR,
            'printf'        => Output::ERROR,
        );

        foreach ($warn_array as $err => $level)
        {
            if ($name == $err)
            {
                $this->addMessage($level, sprintf('Using %s on line %s', $err, $node->getAttribute('startLine')));
                return;
            }
        }
        $this->output->printErrorLevel();
    }

    /**
     * Validate a language file.
     * @param array $node
     */
    private function parseLangNodes(array $node)
    {
        $this->parseNode($node);
    }

    /**
     * Add a new Message to Messages.
     * The filename is automaticlly added.
     *
     * @param $type
     * @param $message
     */
    private function addMessage($type, $message)
    {
        $this->output->addMessage($type, sprintf("%s in %s", $message, $this->file->getFilename()));
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
