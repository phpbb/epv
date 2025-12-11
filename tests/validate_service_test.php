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
use PHPUnit\Framework\TestCase;

class validate_service_test extends TestCase
{
	public static function setUpBeforeClass(): void
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

	public static function good_service_names_data()
	{
		return [
			['simple'],
			['autowired'],
		];
	}

	/**
	 * @dataProvider good_service_names_data
	 */
	public function test_good_service_names($config)
	{
		$this->validateConfig($config, function($output)
		{
			/** @var PHPUnit\Framework\MockObject\MockObject $output */
			$output
				->expects($this->never())
				->method('addMessage')
			;
		});
	}

	public static function bad_service_names_data()
	{
		return [
			['badname1', OutputInterface::ERROR],   // service name starts with phpbb.
			['badname2', OutputInterface::FATAL],   // service name starts with core.
			['badname3', OutputInterface::WARNING], // service name does not match vendor.package
		];
	}

	/**
	 * @dataProvider bad_service_names_data
	 */
	public function test_bad_service_names($config, $expected)
	{
		$this->validateConfig($config, function($output) use ($expected) {
			/** @var PHPUnit\Framework\MockObject\MockObject $output */
			$output
				->expects($this->exactly(4))
				->method('addMessage')
				->with($expected)
			;
		});
	}
}
