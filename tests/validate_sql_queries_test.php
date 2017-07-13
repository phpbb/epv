<?php
/**
 *
 * EPV :: The phpBB Forum Extension Pre Validator.
 *
 * @copyright (c) 2017 phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

class validate_sql_queries_test extends PHPUnit_Framework_TestCase
{
	public static function setUpBeforeClass()
	{
		require_once('./tests/Mock/Output.php');
	}
	
	public function test_insecure_sql_query() {
		$output = $this->getMock('Phpbb\Epv\Output\OutputInterface');
		$output->expects($this->once())
			->method('addMessage')
			->with(\Phpbb\Epv\Output\OutputInterface::WARNING, 'Found potential SQL injection on line 5 in tests/testFiles/sql_injectionphp')
		;

		$file_loader = new \Phpbb\Epv\Files\FileLoader(new \Phpbb\Epv\Tests\Mock\Output(), false, '.', '.');
		$file = $file_loader->loadFile('tests/testFiles/sql_injection.php');

		$tester = new \Phpbb\Epv\Tests\Tests\epv_test_validate_sql_queries(false, $output, '/a/b/', 'epv/test', false, '/a/');
		$tester->validateFile($file);
	}
}
