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
use epv\Files\Type\ComposerFile;
use epv\Files\Type\ComposerFileInterface;
use epv\Output\Output;
use epv\Output\OutputInterface;
use epv\Tests\BaseTest;
use epv\Tests\Exception\TestException;

class epv_test_validate_composer  extends BaseTest{

    public function __construct($debug, OutputInterface $output, $basedir)
    {
        parent::__construct($debug, $output, $basedir);

        $this->fileTypeFull = Type::TYPE_COMPOSER;
    }

    /**
     * @param FileInterface $file
     * @throws \epv\Tests\Exception\TestException
     */
    public function validateFile(FileInterface $file)
    {
        if (!$file instanceof ComposerFileInterface)
        {
            throw new TestException('This test expects a php type, but found something else.');
        }
        if (!$file->getJson() || !is_array($file->getJson()))
        {
            throw new TestException('Parsing composer file failed');
        }
        $this->file = $file;

        $this->validateName($file);
    }

    private function validateName(ComposerFileInterface $file)
    {
        $json = $file->getJson();
        $this->addMessageIfBooleanTrue(!isset($json['name']), Output::FATAL, 'The name key is missing');
        $this->addMessageIfBooleanTrue(strpos('_', $json['name']) !== false, Output::FATAL, 'The namespace should not contain underscores');

    }

    private function addMessageIfBooleanTrue($addMessage, $type, $message)
    {
        if ($addMessage)
        {
            $this->output->addMessage($type, $message, $this->file);
        }
        else
        {
            $this->output->printErrorLevel();
        }
    }

    public function testName()
    {
        return "Validate composer structure";
    }
}
