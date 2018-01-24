<?php
/**
 *
 * EPV :: The phpBB Forum Extension Pre Validator.
 *
 * @copyright (c) 2014 phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

class validate_directory_structure extends PHPUnit_Framework_TestCase {
	public function test_missing_license() {
		$output = $this->getOutputMock();
		$output->expects($this->once())
			->method('addMessage')
			->with(Phpbb\Epv\Output\OutputInterface::ERROR, 'Missing required license.txt file')
		;

		$tester = new \Phpbb\Epv\Tests\Tests\epv_test_validate_directory_structure(false, $output, '/a/b/epv/test', 'epv/test', false, '/a/');
		$tester->validateDirectory(array(), false);
	}

	public function test_license() {
		$output = $this->getOutputMock();
		$output->expects($this->never())
		       ->method('addMessage')
		       ->with(Phpbb\Epv\Output\OutputInterface::ERROR, 'Missing required license.txt file')
		;
		$output->expects($this->never())
			->method('addMessage')
		;

		$tester = new \Phpbb\Epv\Tests\Tests\epv_test_validate_directory_structure(false, $output, '/a/b/epv/test', 'epv/test', false, '/a/b/');
		$tester->validateDirectory(array(
			'/a/b/epv/test/license.txt',
		), false);
	}

	public function test_composer() {
		$output = $this->getOutputMock();
		$output->expects($this->never())
		       ->method('addMessage')
		;

		$tester = new \Phpbb\Epv\Tests\Tests\epv_test_validate_directory_structure(false, $output, '/a/b/epv/test', 'epv/test', false, '/a/b/');
		$tester->validateDirectory(array(
			'/a/b/epv/test/composer.json',
			'/a/b/epv/test/license.txt',
		), false);
	}

	public function test_composer_wrong2() {
		$output = $this->getOutputMock();
		$output->expects($this->once())
			->method('addMessage')
			->with(\Phpbb\Epv\Output\OutputInterface::ERROR,
				sprintf("Packaging structure doesn't meet the extension DB policies.\nExpected: %s\nGot: %s",
				'epv/test', 'b/epv/test'))
		;
		$tester = new \Phpbb\Epv\Tests\Tests\epv_test_validate_directory_structure(false, $output, '/a/b/epv/test', 'epv/test', false, '/a/');
		$tester->validateDirectory(array(
			'/a/b/epv/test/composer.json',
			'/a/b/epv/test/license.txt',
		), false);
	}


	public function test_composer_wrong() {
		$output = $this->getOutputMock();

		$output->expects($this->exactly(1))
			->method('addMessage')
			->with(\Phpbb\Epv\Output\OutputInterface::ERROR,
				sprintf("Packaging structure doesn't meet the extension DB policies.\nExpected: %s\nGot: %s",
				'epv/test', 'b'))
		;

		$tester = new \Phpbb\Epv\Tests\Tests\epv_test_validate_directory_structure(false, $output, '/a/b/', 'epv/test', false, '/a/');
		$tester->validateDirectory(array(
			'/a/b/composer.json',
			'/a/b/epv/test/license.txt',
		), false);
	}

	/**
	 * @return \PHPUnit_Framework_MockObject_MockObject
	 */
	function getOutputMock()
	{
		return $this->getMock('Phpbb\Epv\Output\OutputInterface');
	}

}
