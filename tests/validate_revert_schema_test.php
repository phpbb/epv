<?php

/**
 *
 * EPV :: The phpBB Forum Extension Pre Validator.
 *
 * @copyright (c) 2017 phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

use Phpbb\Epv\Files\FileLoader;
use Phpbb\Epv\Output\OutputInterface;
use Phpbb\Epv\Tests\Mock\Output;
use Phpbb\Epv\Tests\Tests\epv_test_validate_revert_schema;
use PHPUnit\Framework\TestCase;

class validate_revert_schema_test extends TestCase
{
	public static function setUpBeforeClass(): void
	{
		require_once('./tests/Mock/Output.php');
	}

	/**
	 * @param string   $file
	 * @param callable $configure
	 */
	private function validateFile($file, $configure) {
		/** @var OutputInterface $output */
		$output = $this->createMock(OutputInterface::class);
		$configure($output);

		$file_loader = new FileLoader(new Output(), false, '.', '.');
		$file = $file_loader->loadFile($file);

		$tester = new epv_test_validate_revert_schema(false, $output, '/a/b/', 'epv/test', false, '/a/');
		$tester->validateFile($file);
	}

	public function test_missing_update_schema()
	{
		$this->validateFile('tests/testFiles/migrations/missing_update_schema.php', function($output)
		{
			/** @var PHPUnit\Framework\MockObject\MockObject $output */
			$output->expects($this->never())
				->method('addMessage');
		});
	}

	public function test_existing_revert_schema()
	{
		$this->validateFile('tests/testFiles/migrations/existing_revert_schema.php', function($output)
		{
			/** @var PHPUnit\Framework\MockObject\MockObject $output */
			$output->expects($this->never())
				->method('addMessage');
		});
	}

	public function test_missing_revert_schema()
	{
		$this->validateFile('tests/testFiles/migrations/missing_revert_schema.php', function($output)
		{
			/** @var PHPUnit\Framework\MockObject\MockObject $output */
			$output->expects($this->once())
				->method('addMessage')
				->with(OutputInterface::ERROR, 'Migration file tests/testFiles/migrations/missing_revert_schema.php is missing the revert_schema() method.');
		});
	}
}
