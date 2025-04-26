<?php

use Phpbb\Epv\Output\OutputInterface;
use Phpbb\Epv\Files\FileLoader;
use Phpbb\Epv\Tests\Mock\Output;
use Phpbb\Epv\Tests\Tests\epv_test_validate_php_functions;
use PHPUnit\Framework\TestCase;

/**
 *
 * EPV :: The phpBB Forum Extension Pre Validator.
 *
 * @copyright (c) 2014 phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

class epv_test_validate_php_functions_test extends TestCase
{
	public static function setUpBeforeClass(): void
	{
		require_once('./tests/Mock/Output.php');
	}

	public function test_usage_of_enable_globals() {
		$output = $this->getOutputMock();
		$output->expects(self::exactly(2))
			->method('addMessage')
			->with(OutputInterface::FATAL, 'The use of enable_super_globals() is not allowed for security reasons on line 7 in tests/testFiles/enable_globals.php')
		;

		$file = $this->getLoader()->loadFile('tests/testFiles/enable_globals.php');

		$tester = new epv_test_validate_php_functions(false, $output, '/a/b/', 'epv/test', false, '/a/');
		$tester->validateFile($file);
	}

	public function test_usage_of_enable_globals2() {
		$output = $this->getOutputMock();
		$output->expects(self::exactly(0))
			->method('addMessage')
			 ;

		$file = $this->getLoader()->loadFile('tests/testFiles/enable_globals2.php');

		$tester = new epv_test_validate_php_functions(false, $output, '/a/b/', 'epv/test', false, '/a/');
		$tester->validateFile($file);
	}

	public function test_usage_of_enable_globals3() {
		$output = $this->getOutputMock();
		$output->expects(self::once())
			->method('addMessage')
			->with(OutputInterface::FATAL, 'The use of enable_super_globals() is not allowed for security reasons on line 7 in tests/testFiles/enable_globals3.php')
		;

		$file = $this->getLoader()->loadFile('tests/testFiles/enable_globals3.php');

		$tester = new epv_test_validate_php_functions(false, $output, '/a/b/', 'epv/test', false, '/a/');
		$tester->validateFile($file);
	}

	public function test_usage_of_addslashes() {
		$output = $this->getOutputMock();
		$output->expects(self::exactly(2))
			->method('addMessage')
			->with(OutputInterface::ERROR, 'Using addslashes on line 8 in tests/testFiles/addslashes.php')
		;

		$file = $this->getLoader()->loadFile('tests/testFiles/addslashes.php');

		$tester = new epv_test_validate_php_functions(false, $output, '/a/b/', 'epv/test', false, '/a/');
		$tester->validateFile($file);
	}

	public function test_usage_of_evals() {
		$output = $this->getOutputMock();
		$output->expects(self::once())
			->method('addMessage')
			->with(OutputInterface::FATAL, 'The use of eval() is not allowed for security reasons on line 8 in tests/testFiles/eval.php')
		;

		$file = $this->getLoader()->loadFile('tests/testFiles/eval.php');

		$tester = new epv_test_validate_php_functions(false, $output, '/a/b/', 'epv/test', false, '/a/');
		$tester->validateFile($file);
	}

	public function test_usage_of_no_inphpbb() {
		$output = $this->getOutputMock();
		$output->expects(self::once())
			->method('addMessage')
			->with(OutputInterface::WARNING, 'IN_PHPBB is not defined in tests/testFiles/no_in_phpbb.php')
		;

		$file = $this->getLoader()->loadFile('tests/testFiles/no_in_phpbb.php');

		$tester = new epv_test_validate_php_functions(false, $output, '/a/b/', 'epv/test', false, '/a/');
		$tester->validateFile($file);
	}

	public function test_usage_of_wrong_in_phpbb() {
		$output = $this->getOutputMock();
		$output->expects(self::exactly(2))
			->method('addMessage')
		;

		$file = $this->getLoader()->loadFile('tests/testFiles/in_phpbb_wrong.php');

		$tester = new epv_test_validate_php_functions(false, $output, '/a/b/', 'epv/test', false, '/a/');
		$tester->validateFile($file);
	}

	public function test_usage_of_wrong_in_phpbb2() {
		$output = $this->getOutputMock();
		$output->expects(self::exactly(5))
			->method('addMessage')
		;

		$file = $this->getLoader()->loadFile('tests/testFiles/in_phpbb_wrong2.php');

		$tester = new epv_test_validate_php_functions(false, $output, '/a/b/', 'epv/test', false, '/a/');
		$tester->validateFile($file);
	}

	public function test_usage_of_namespace() {
		$output = $this->getOutputMock();
		$output->expects(self::exactly(2))
			->method('addMessage')
		;

		$file = $this->getLoader()->loadFile('tests/testFiles/no_namespace.php');

		$tester = new epv_test_validate_php_functions(false, $output, '/a/b/', 'epv/test', false, '/a/');
		$tester->validateFile($file);
	}

	public function test_usage_of_var_test() {
		$output = $this->getOutputMock();
		$output->expects(self::exactly(0))
			->method('addMessage')
		;

		$file = $this->getLoader()->loadFile('tests/testFiles/var_test.php');

		$tester = new epv_test_validate_php_functions(false, $output, '/a/b/', 'epv/test', false, '/a/');
		$tester->validateFile($file);
	}

	public function test_usage_of_var_test2() {
		$output = $this->getOutputMock();
		$output->expects(self::exactly(0))
			->method('addMessage')
		;

		$file = $this->getLoader()->loadFile('tests/testFiles/var_test2.php');

		$tester = new epv_test_validate_php_functions(false, $output, '/a/b/', 'epv/test', false, '/a/');
		$tester->validateFile($file);
	}

	public function test_usage_variable() {
		$output = $this->getOutputMock();
		$output->expects(self::exactly(0))
			->method('addMessage')
		;

		$file = $this->getLoader()->loadFile('tests/testFiles/variable_function.php');

		$tester = new epv_test_validate_php_functions(false, $output, '/a/b/', 'epv/test', false, '/a/');
		$tester->validateFile($file);
	}

	private function getLoader()
	{
		return new FileLoader(new Output(), false, '.', '.');
	}

	/**
	 * @return PHPUnit\Framework\MockObject\MockObject|OutputInterface
	 */
	function getOutputMock()
	{
		return $this->createMock(OutputInterface::class);
	}
}
