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
use epv\Files\Type\ServiceFileInterface;
use epv\Output\Messages;
use epv\Output\OutputInterface;
use epv\Tests\BaseTest;
use epv\Tests\Exception\TestException;

class epv_test_validate_service extends BaseTest {


    public function __construct($debug, OutputInterface $output)
    {
        parent::__construct($debug, $output);

        $this->fileTypeFull = Type::TYPE_SERVICE;
    }

    public function validateFile(FileInterface $file)
    {
        if (!$file instanceof ServiceFileInterface)
        {
            throw new TestException("This tests except a service type, but got something else?");
        }
        $this->validate($file);
    }

    /**
     * Do the actual validation of the service file.
     * @param ServiceFileInterface $file
     */
    private function validate(ServiceFileInterface $file)
    {
        $yml = $file->getYaml();

        if (sizeof($yml) > 1)
        {
            Messages::addMessage(Messages::NOTICE, "Service contains more as 1 key on root level");
        }

        if (!isset ($yml['services']))
        {
            Messages::addMessage(Messages::WARNING, "Service doesn't contain 'services' key");
        }
    }

    /**
     *
     * @return String
     */
    public function testName()
    {
        return "Validate service";
    }

} 