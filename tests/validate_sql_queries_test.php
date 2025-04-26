<?php

use Phpbb\Epv\Files\FileLoader;
use Phpbb\Epv\Output\OutputInterface;
use Phpbb\Epv\Tests\Mock\Output;
use Phpbb\Epv\Tests\Tests\epv_test_validate_sql_queries;
use PHPUnit\Framework\TestCase;

/**
 *
 * EPV :: The phpBB Forum Extension Pre Validator.
 *
 * @copyright (c) 2017 phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

class validate_sql_queries_test extends TestCase
{
	public static function setUpBeforeClass(): void
	{
		require_once('./tests/Mock/Output.php');
	}

	public function test_insecure_sql_query() {
		$output = $this->createMock(OutputInterface::class);
		$output->expects(self::once())
			->method('addMessage')
			->with(OutputInterface::WARNING, 'Found potential SQL injection on line 5 in tests/testFiles/sql_injection.php')
		;

		$file_loader = new FileLoader(new Output(), false, '.', '.');
		$file = $file_loader->loadFile('tests/testFiles/sql_injection.php');

		$tester = new epv_test_validate_sql_queries(false, $output, '/a/b/', 'epv/test', false, '/a/');
		$tester->validateFile($file);
	}
}
