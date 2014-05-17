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
use PhpParser\Parser;

class epv_test_validate_php_functions extends BaseTest
{
    private $parser;

    private $in_phpbb = false;

    public function __construct($debug, OutputInterface $output, $basedir)
    {
        parent::__construct($debug, $output, $basedir);

        $this->fileTypeFull = Type::TYPE_PHP;
        $this->parser = new Parser(new Emulative());
    }

    /**
     * @param FileInterface $file
     * @throws \epv\Tests\Exception\TestException
     */
    public function validateFile(FileInterface $file)
    {
        if (!$file instanceof PHPFileInterface)
        {
            throw new TestException("This test expects a service type, but found something else.");
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
        $this->in_phpbb = false;

        try
        {
            $stmt = $this->parser->parse($file->getFile());

            if ($file instanceof LangFile)
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
                $ok = true;
                // Lets see if there is just a namespace + class
                if (sizeof($stmt) == 1 && $stmt[0] instanceof Namespace_)
                {
                    foreach ($stmt[0]->stmts as $st)
                    {
                        if ($st instanceof Class_ || $st instanceof Interface_ || $st instanceof Use_)
                        {
                            continue;
                        }
                        $ok = false;
                    }
                }
                else
                {
                    $ok = false;
                }

                if ($this->isTest())
                {
                    // We skip tests.
                    $this->output->writelnIfDebug(sprintf("Skipped %s because of test file.", $file->getFilename()));
                    $ok = true;
                }

                if (!$ok)
                {
                    $this->addMessage(Output::WARNING, "IN_PHPBB is not defined.");
                }
                else
                {
                    $this->output->writelnIfDebug(sprintf("Did not find IN_PHPBB, but file (%s) only contains classes or interfaces.", $file->getFilename()));
                }
            }
        }
        catch (Error $e) // Catch PhpParser error.
        {
            $this->output->addMessage(Output::FATAL, "PHP parse error in file " . $file->getFilename() . '. Message: ' . $e->getMessage());
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
            foreach ($nodes as $node)
            {
                // Check if there is a class.
                // If there is a class, there should be a namespace.
                if ($node instanceof Class_ || $node instanceof Interface_)
                {
                    $this->addMessage($this->isTest() ? Output::NOTICE : Output::ERROR, "All files with a class or an interface should have a namespace.");
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
                $this->addMessage(Output::WARNING, "Besides the namespace, there should be no other statements.");
            }
        }
    }

    /**
     * Validate the structure in a namespace, or in the full file if it is a non namespaced file.
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
                    continue;
                }
            }
            if ($node instanceof Exit_)
            {
                $this->addMessage(Output::WARNING, sprintf('Using exit on line %s.', $node->getAttribute("startLine")));
            }
            if ($node instanceof Print_ || $node instanceof Echo_)
            {
                $this->addMessage(Output::ERROR, sprintf('The template system should be used instead of echo or print on line %s.', $node->getAttribute("startLine")));
            }
            $warn_array = array(
                'die',
                'md5',
                'eval'
            );
            foreach ($warn_array as $err)
            {
                if ($node instanceof FuncCall && $node->name == $err)
                {
                    $this->addMessage(Output::WARNING, sprintf('Using %s on line %s.', $err, $node->getAttribute("startLine")));
                }
            }

            if (sizeof($node->stmts))
            {
                $this->parseNode($node->stmts);
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
            if ($node->stmts[0] instanceof Exit_)
            {
                // Found IN_PHPBB
                $this->in_phpbb = true;
            }
            else
            {
                // Found IN_PHPBB, but it didn't exists?
                // We dont set $this->in_phpbb, so parseNode continue running on this node.
                // Also include a notice.
                $this->addMessage(Output::NOTICE, "IN_PHPBB check should exit if it is not defined.");
            }
            if (sizeof($node->stmts) > 1)
            {
                $this->addMessage(Output::WARNING, "There should be no other statements, such as exit, in the IN_PHPBB check.");
                unset($node->stmts[0]);
                $this->parseNode($node->stmts);
            }
        }
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
        return "Validate disallowed php functions";
    }
} 