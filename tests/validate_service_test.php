<?php
/**
 *
 * EPV :: The phpBB Forum Extension Pre Validator.
 *
 * @copyright (c) 2021 phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

use Phpbb\Epv\Files\FileLoader;
use Phpbb\Epv\Files\Type\ServiceFile;
use Phpbb\Epv\Tests\Mock\Output;
use Phpbb\Epv\Output\OutputInterface;
use Phpbb\Epv\Tests\Tests\epv_test_validate_service;

class validate_service_test extends PHPUnit_Framework_TestCase
{
	public static function setUpBeforeClass()
	{
		require_once('./tests/Mock/Output.php');
	}

	/**
	 * @param string   $config    The config dir name
	 * @param callable $configure Call to validation function
	 */
	private function validateConfig($config, $configure)
	{
		/** @var OutputInterface $output */
		$output = $this->createMock(OutputInterface::class);
		$configure($output);

		$file_loader = new FileLoader(new Output(), false, '.', '.');
		$file = $file_loader->loadFile('tests/testFiles/configs/' . $config . '/services.yml');
		self::assertInstanceOf(ServiceFile::class, $file);

		$tester = new epv_test_validate_service(false, $output, 'tests/testFiles/', 'epv/test', false, 'tests/testFiles/');
		$tester->validateFile($file);
	}

	public function services_good_data()
	{
		return [
			['simple'],
			['autowired'],
		];
	}

	/**
	 * @dataProvider services_good_data
	 */
	public function test_services_good1($config)
	{
		$this->validateConfig($config, function($output)
		{
			/** @var PHPUnit_Framework_MockObject_MockObject $output */
			$output
				->expects($this->never())
				->method('addMessage')
			;
		});
	}

	public function services_bad_data()
	{
		return [
			['badname1', OutputInterface::ERROR],   // service name starts with phpbb.
			['badname2', OutputInterface::FATAL],   // service name starts with core.
			['badname3', OutputInterface::WARNING], // service name does not match vendor.package
			['badname4', OutputInterface::WARNING], // service name case does not match vendor.package
		];
	}

	/**
	 * @dataProvider services_bad_data
	 */
	public function test_services_with_phpbb($config, $expected)
	{
		$this->validateConfig($config, function($output) use ($expected) {
			/** @var PHPUnit_Framework_MockObject_MockObject $output */
			$output
				->expects($this->exactly(4))
				->method('addMessage')
				->with($expected)
			;
		});
	}
}
