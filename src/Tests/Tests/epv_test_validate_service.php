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
use Phpbb\Epv\Files\Type\ServiceFileInterface;
use Phpbb\Epv\Output\Output;
use Phpbb\Epv\Output\OutputInterface;
use Phpbb\Epv\Tests\BaseTest;
use Phpbb\Epv\Tests\Exception\TestException;
use Phpbb\Epv\Tests\Type;

class epv_test_validate_service extends BaseTest
{


	public function __construct($debug, OutputInterface $output, $basedir, $namespace, $titania)
	{
		parent::__construct($debug, $output, $basedir, $namespace, $titania);

		$this->fileTypeFull = Type::TYPE_SERVICE;
	}

	public function validateFile(FileInterface $file)
	{
		if (!$file instanceof ServiceFileInterface)
		{
			throw new TestException("This test expects a service type, but found something else.");
		}
		$this->validate($file);
	}

	/**
	 * Do the actual validation of the service file.
	 *
	 * @param ServiceFileInterface $file
	 */
	private function validate(ServiceFileInterface $file)
	{
		$yml = $file->getYaml();

		if (!isset ($yml['services']))
		{
			$this->output->addMessage(Output::WARNING, "Service does not contain a 'services' key.");
		}

		if (is_array($yml['services']))
		{
			foreach ($yml['services'] as $key => $service)
			{
				$this->validateServiceName($key, $file);
			}
		}
	}


	/**
	 * Validate the service name to match the requirements for services.
	 *
	 * @param string                                     $service service name to validate
	 * @param \Phpbb\Epv\Files\Type\ServiceFileInterface $file
	 */
	private function validateServiceName($service, ServiceFileInterface $file)
	{
		$vendor = str_replace('/', '.', $this->namespace);

		if (strtolower(substr($service, 0, 5)) == 'phpbb')
		{
			$this->output->addMessage(Output::ERROR, sprintf('The phpbb vendorname should only be used for official extensions in service names in %s. Current service name: %s', $file->getSaveFilename(), $service));
		}
		else if (strtolower(substr($service, 0, 4)) == 'core')
		{
			$this->output->addMessage(Output::FATAL, sprintf('The core vendorname should not be used in event names in %s. Current event name: %s', $file->getSaveFilename(), $service));
		}
		if (substr($service, 0, strlen($vendor)) != $vendor)
		{
			$this->output->addMessage(Output::WARNING, sprintf('The service name should start with vendor.namespace (which is %s) but started with %s in %s', $vendor, $service, $file->getSaveFilename()));
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
