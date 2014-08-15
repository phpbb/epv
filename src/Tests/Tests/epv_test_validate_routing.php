<?php
/**
 *
 * @package       EPV
 * @copyright (c) 2014 phpBB Group
 * @license       http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */
namespace Phpbb\Epv\Tests\Tests;


use Phpbb\Epv\Files\FileInterface;
use Phpbb\Epv\Files\Type\RoutingFileInterface;
use Phpbb\Epv\Output\Output;
use Phpbb\Epv\Output\OutputInterface;
use Phpbb\Epv\Tests\BaseTest;
use Phpbb\Epv\Tests\Exception\TestException;
use Phpbb\Epv\Tests\Type;

class epv_test_validate_routing extends BaseTest
{


	public function __construct($debug, OutputInterface $output, $basedir, $namespace, $titania)
	{
		parent::__construct($debug, $output, $basedir, $namespace, $titania);

		$this->fileTypeFull   = Type::TYPE_ROUTING;
		$this->totalFileTests = 0;
	}

	public function validateFile(FileInterface $file)
	{
		if (!$file instanceof RoutingFileInterface)
		{
			throw new TestException("This test expects a routing type, but found something else.");
		}
		$this->validate($file);
	}

	/**
	 * Do the actual validation of the routing file.
	 *
	 * @param RoutingFileInterface $file
	 */
	private function validate(RoutingFileInterface $file)
	{
		$yml = $file->getYaml();

		if (is_array($yml))
		{
			$this->output->inMaxPogress(sizeof($yml) * 2);

			foreach ($yml as $key => $route)
			{
				$this->validateRoutingName($key, $file);
			}
		}
	}

	/**
	 * Validate the route name to match the requirements for routes.
	 *
	 * @param string                                     $route route name to validate
	 * @param \Phpbb\Epv\Files\Type\RoutingFileInterface $file
	 */
	private function validateRoutingName($route, RoutingFileInterface $file)
	{
		$vendor = str_replace('/', '_', $this->namespace);

		if (strtolower(substr($route, 0, 5)) == 'phpbb')
		{
			$this->output->addMessage(Output::ERROR, sprintf('The phpbb vendorname should only be used for official extensions in route names in %s. Current service name: %s', $file->getFilename(), $route));
		}
		else if (strtolower(substr($route, 0, 4)) == 'core')
		{
			$this->output->addMessage(Output::FATAL, sprintf('The core vendorname should not be used in route names in %s. Current route name: %s', $file->getFilename(), $route));
		}
		else
		{
			$this->output->printErrorLevel();
		}
		if (substr($route, 0, strlen($vendor)) != $vendor)
		{
			$this->output->addMessage(Output::WARNING, sprintf('The route name should start with vendor_namespace (which is %s) but started with %s in %s', $vendor, $route, $file->getFilename()));
		}
		else
		{
			$this->output->printErrorLevel();
		}
	}

	/**
	 *
	 * @return String
	 */
	public function testName()
	{
		return "Validate route";
	}
}
