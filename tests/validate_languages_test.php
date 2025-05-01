<?php

/**
 *
 * EPV :: The phpBB Forum Extension Pre Validator.
 *
 * @copyright (c) 2017 phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

use Phpbb\Epv\Output\OutputInterface;
use Phpbb\Epv\Tests\Tests\epv_test_validate_languages;
use PHPUnit\Framework\TestCase;

class validate_languages_test extends TestCase
{
	public static function setUpBeforeClass(): void
	{
		require_once('./tests/Mock/Output.php');
	}

	public function test_languages() {
		/** @var OutputInterface|PHPUnit\Framework\MockObject\MockObject\MockObject $output */
		$output = $this->createMock(OutputInterface::class);

		$output
			->expects(self::exactly(2))
			->method('addMessage')
			->withConsecutive(
			 	[OutputInterface::NOTICE, 'Language en_incomplete is missing the language file additional.php'],
				[OutputInterface::WARNING, 'Language file en_incomplete/common.php is missing the language key B']
			)
		;

		$tester = new epv_test_validate_languages(false, $output, 'tests/testFiles/', 'epv/test', false, 'tests/testFiles/');
		$tester->validateDirectory([
			'tests/testFiles/language/en/common.php',
			'tests/testFiles/language/en/additional.php',
			'tests/testFiles/language/en_complete/common.php',
			'tests/testFiles/language/en_complete/additional.php',
			'tests/testFiles/language/en_incomplete/common.php',
		]);
	}

	public function test_missing_en_languages() {
		/** @var OutputInterface|PHPUnit\Framework\MockObject\MockObject\MockObject $output */
		$output = $this->createMock(OutputInterface::class);

		$output
			->expects(self::once())
			->method('addMessage')
			->with(OutputInterface::FATAL, 'English language pack is missing')
		;

		$tester = new epv_test_validate_languages(false, $output, 'tests/testFiles/', 'epv/test', false, 'tests/testFiles/');
		$tester->validateDirectory([
			'tests/testFiles/language/en_us/common.php',
			'tests/testFiles/language/en_us/additional.php',
		]);
	}
}
