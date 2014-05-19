<?php
/**
 *
 * @package EPV
 * @copyright (c) 2014 phpBB Group
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */
namespace epv\Tests;


use epv\Files\FileInterface;
use epv\Files\LineInterface;
use epv\Output\OutputInterface;
use epv\Tests\Exception\TestException;

abstract class BaseTest implements TestInterface {
    private $debug;
    protected  $fileTypeLine;
    protected  $fileTypeFull;

    // Current file. Used in some tests.
    protected  $file;

    /**
     * If this is set to true, tests are run on full directory listings.
     * @var bool
     */
    protected  $directory = false;
    /**
     * @var \epv\Output\OutputInterface
     */
    protected  $output;
    /**
     * @var
     */
    protected  $basedir;

    /**
     * @param $debug
     * @param \epv\Output\OutputInterface $output
     * @param $basedir
     */
    public function __construct($debug, OutputInterface $output, $basedir)
    {
        $this->debug = $debug;
        $this->output = $output;
        $this->basedir = $basedir;
    }

    /**
     *
     * @param \epv\Files\LineInterface $line
     * @throws Exception\TestException
     * @internal param $
     */
    public function validateLine(LineInterface $line)
    {
        throw new TestException("Test declared to be a line test, but doesn't implement validateLine.");
    }

    /**
     * @param \epv\Files\FileInterface $file
     * @throws Exception\TestException
     * @internal param $
     */
    public function validateFile(FileInterface $file)
    {
        throw new TestException("Test declared to be a file test, but doesn't implement validateFile.");
    }

    /**
     * @param array $dirList
     * @return mixed|void
     * @throws Exception\TestException
     */
    public function validateDirectory(array $dirList)
    {
        throw new TestException("Test declared to be a directory listing test, but doesn't implement validateDirectory.");
    }

    /**
     * @param int $type
     * @return bool
     */
    public function doValidateLine($type)
    {
        return $this->fileTypeLine & $type;
    }

    /**
     * @param int $type
     * @return bool
     */
    public function doValidateFile($type)
    {
        return $this->fileTypeFull & $type;
    }

    /**
     * @return bool
     */
    public function doValidateDirectory()
    {
        return $this->directory;
    }


    /**
     * Convert a boolean to Yes or No.
     *
     * @param $bool
     * @return string
     */
    private function boolToLang($bool)
    {
        return $bool ? "Yes" : "No";
    }

    /**
     * @return string
     */
    public function __toString()
    {
        $string = 'Test: ' . $this->testName() . '. ';

        return $string;
    }

    /**
     * Checks to see if the current file is for tests.
     * @param FileInterface $file
     * @return bool
     */
    protected  function isTest(FileInterface $file = null)
    {
        if ($file == null)
        {
            $file = $this->file;
        }

        $dir = str_replace($this->basedir, '', $file->getFilename());
        $dir = explode("/", $dir);

        return $dir[0] == 'test' || $dir[0] == 'tests';
    }
}
