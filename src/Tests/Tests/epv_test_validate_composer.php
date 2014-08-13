<?php
/**
 *
 * @package EPV
 * @copyright (c) 2014 phpBB Group
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */
namespace Phpbb\Epv\Tests\Tests;

use Phpbb\Epv\Files\FileInterface;
use Phpbb\Epv\Files\Type\ComposerFile;
use Phpbb\Epv\Files\Type\ComposerFileInterface;
use Phpbb\Epv\Output\Output;
use Phpbb\Epv\Output\OutputInterface;
use Phpbb\Epv\Tests\BaseTest;
use Phpbb\Epv\Tests\Type;
use Phpbb\Epv\Tests\Exception\TestException;

class epv_test_validate_composer  extends BaseTest{

    public function __construct($debug, OutputInterface $output, $basedir, $namespace, $titania)
    {
        parent::__construct($debug, $output, $basedir, $namespace, $titania);

        $this->fileTypeFull = Type::TYPE_COMPOSER;
        $this->totalFileTests = 2;
    }

    /**
     * @param FileInterface $file
     * @throws \Phpbb\Epv\Tests\Exception\TestException
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
        $this->addMessageIfBooleanTrue(strpos($json['name'], '_') !== false, Output::ERROR, 'The namespace should not contain underscores');

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
