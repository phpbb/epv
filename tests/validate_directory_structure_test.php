<?php

use Phpbb\Epv\Output\OutputInterface;
use Phpbb\Epv\Tests\Tests\epv_test_validate_directory_structure;
use PHPUnit\Framework\TestCase;

/**
 *
 * EPV :: The phpBB Forum Extension Pre Validator.
 *
 * @copyright (c) 2014 phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

class validate_directory_structure_test extends TestCase {
	public function test_missing_license() {
		$output = $this->getOutputMock();
		$output->expects(self::once())
			->method('addMessage')
			->with(OutputInterface::ERROR, 'Missing required license.txt file')
		;

		$tester = new epv_test_validate_directory_structure(false, $output, '/a/b/epv/test', 'epv/test', false, '/a/');
		$tester->validateDirectory(array(), false);
	}

	public function test_license() {
		$output = $this->getOutputMock();
		$output->expects(self::never())
		   ->method('addMessage')
		   ->with(OutputInterface::ERROR, 'Missing required license.txt file')
		;
		$output->expects(self::never())
			->method('addMessage')
		;

		$tester = new epv_test_validate_directory_structure(false, $output, '/a/b/epv/test', 'epv/test', false, '/a/b/');
		$tester->validateDirectory(array(
			'/a/b/epv/test/license.txt',
		), false);
	}

	public function test_composer() {
		$output = $this->getOutputMock();
		$output->expects(self::never())
		   ->method('addMessage')
		;

		$tester = new epv_test_validate_directory_structure(false, $output, '/a/b/epv/test', 'epv/test', false, '/a/b/');
		$tester->validateDirectory(array(
			'/a/b/epv/test/composer.json',
			'/a/b/epv/test/license.txt',
		), false);
	}

	public function test_composer_wrong2() {
		$output = $this->getOutputMock();
		$output->expects(self::once())
			->method('addMessage')
			->with(OutputInterface::ERROR,
				sprintf("Packaging structure doesn't meet the extension DB policies.\nExpected: %s\nGot: %s",
				'epv/test', 'b/epv/test'))
		;
		$tester = new epv_test_validate_directory_structure(false, $output, '/a/b/epv/test', 'epv/test', false, '/a/');
		$tester->validateDirectory(array(
			'/a/b/epv/test/composer.json',
			'/a/b/epv/test/license.txt',
		), false);
	}

	public function test_composer_wrong() {
		$output = $this->getOutputMock();

		$output->expects(self::once())
			->method('addMessage')
			->with(OutputInterface::ERROR,
				sprintf("Packaging structure doesn't meet the extension DB policies.\nExpected: %s\nGot: %s",
				'epv/test', 'b'))
		;

		$tester = new epv_test_validate_directory_structure(false, $output, '/a/b/', 'epv/test', false, '/a/');
		$tester->validateDirectory(array(
			'/a/b/composer.json',
			'/a/b/epv/test/license.txt',
		), false);
	}

	/**
	 * @return PHPUnit\Framework\MockObject\MockObject|OutputInterface
	 */
	function getOutputMock()
	{
		return $this->createMock(OutputInterface::class);
	}
}
