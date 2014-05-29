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
use epv\Files\LineInterface;
use epv\Files\Type\LangFile;
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
use PhpParser\Node\Stmt;
use PhpParser\Node;
use PhpParser\Parser;

class epv_test_validate_deprecated_functions extends BaseTest
{
    private $parser;


    public function __construct($debug, OutputInterface $output, $basedir)
    {
        parent::__construct($debug, $output, $basedir);

        $this->fileTypeFull = Type::TYPE_PHP;
        $this->parser = new Parser(new Emulative());
        $this->totalFileTests = 0;
    }

    /**
     * @param FileInterface $file
     * @throws \epv\Tests\Exception\TestException
     */
    public function validateFile(FileInterface $file)
    {
        if (!$file instanceof PHPFileInterface)
        {
            throw new TestException("This test expects a php type, but found something else.");
        }
        $this->validate($file);
    }

    /**
     * Do the actual validation of the service file.
     * @param PHPFileInterface $file
     */
    private function validate(PHPFileInterface $file)
    {
        $this->output->writelnIfDebug("Attempting to parse file: " . $file->getFilename());

        $this->file = $file;

        try
        {
            $stmt = $this->parser->parse($file->getFile());
            $this->parseNode($stmt);
        }
        catch (Error $e) // Catch PhpParser error.
        {
            // We don't add this error to the list, it is already done in validate_php_functions.
        }
    }

    /**
     * Validate the structure
     * @param array $nodes
     */
    private function parseNode(array $nodes)
    {
        foreach ($nodes as $node)
        {
            if (!$node instanceof Node && $node instanceof Stmt)
            {
                var_dump($node);
                exit;
                continue;
            }
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
                $this->output->inMaxPogress(1);

                $this->output->writelnIfDebug("Found func call");

                $deprecated = array(
                    'gen_email_hash',
                    'cache_moderators',
                    'update_foes',
                    'get_user_avatar',
                    'phpbb_hash',
                    'phpbb_check_hash',
                    'phpbb_clean_path',
                    'set_config',
                    'request_var',
                    'set_config_count',
                    'tz_select',
                    'add_log',

                    'set_var',
                    'get_table',

                );

                if (in_array($name, $deprecated))
                {
                    $this->addMessage(Output::WARNING, sprintf("Found a deprecated function call to %s on line %s", $name, $node->getAttribute("startLine")));
                }
                else
                {
                    $this->output->printErrorLevel();
                }
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
        return "Validate disallowed php functions";
    }
}
