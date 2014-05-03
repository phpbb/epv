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
use epv\Output\Messages;
use epv\Output\OutputInterface;
use epv\Tests\BaseTest;
use epv\Tests\Exception\TestException;
use PhpParser\Error;
use PhpParser\Lexer\Emulative;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Parser;

class epv_test_validate_php_functions extends BaseTest {
    private $parser;
    private $file;


    public function __construct($debug, OutputInterface $output)
    {
        parent::__construct($debug, $output);

        $this->fileTypeFull = Type::TYPE_PHP;
        $this->parser = new Parser(new Emulative());
    }

    public function validateFile(FileInterface $file)
    {
        if (!$file instanceof PHPFileInterface)
        {
            throw new TestException("This tests except a service type, but got something else?");
        }
        $this->validate($file);
    }

    /**
     * Do the actual validation of the service file.
     * @param PHPFileInterface $file
     */
    private function validate(PHPFileInterface $file)
    {
        $this->output->writelnIfDebug("Trying to parse file: " . $file->getFilename());

        $this->file = $file;

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
        }
        catch (Error $e)// Catch PhpParser error.
        {
            Messages::addMessage(Messages::FATAL, "PHP parse error in file " . $file->getFilename() . '. Message: ' . $e->getMessage());
        }
    }

    /**
     * Validate the structure of a php file.
     * @param array $node
     */
    private function parseNodes(array $node)
    {
        if (!($node[0] instanceof Namespace_))
        {
            $this->addMessage(Messages::ERROR, "PHP file contains no namespace. All PHP files with a class should contain a namespace.");
            // We stop validating this file here.
            return;
        }
    }

    /**
     * Validate a language file.
     * @param array $node
     */
    private function parseLangNodes(array $node)
    {
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
        Messages::addMessage($type, sprintf("%s in file %s", $message, $this->file->getFilename()));
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