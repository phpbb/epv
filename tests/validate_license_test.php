<?php

/**
 *
 * EPV :: The phpBB Forum Extension Pre Validator.
 *
 * @copyright (c) 2018 phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

use Phpbb\Epv\Output\OutputInterface;
use Phpbb\Epv\Tests\Tests\epv_test_validate_directory_structure;
use PHPUnit\Framework\TestCase;

class validate_license_test extends TestCase
{
	/**
	 * @param string $license
	 * @param callable $configure
	 */
	private function validateLicense($license, $configure) {
		/** @var OutputInterface $output */
		$output = $this->createMock(OutputInterface::class);
		$configure($output);

		$tester = new epv_test_validate_directory_structure(false, $output, 'tests/testFiles/', 'epv/test', false, 'tests/testFiles/');
		$tester->validateDirectory([
			'tests/testFiles/licenses/' . $license . '/license.txt',
		]);
	}

	public function test_license_gpl_2_0_skeleton()
	{
		$this->validateLicense('gpl-2.0-skeleton-ext', function($output)
		{
			/** @var PHPUnit\Framework\MockObject\MockObject $output */
			$output
				->expects($this->never())
				->method('addMessage')
			;
		});
	}

	public function test_license_gpl_2_0_with_appendix()
	{
		$this->validateLicense('gpl-2.0-with-appendix', function($output)
		{
			/** @var PHPUnit\Framework\MockObject\MockObject $output */
			$output
				->expects($this->never())
				->method('addMessage')
			;
		});
	}

	public function test_license_gpl_3_0()
	{
		$this->validateLicense('gpl-3.0', function($output)
		{
			/** @var PHPUnit\Framework\MockObject\MockObject $output */
			$output
				->expects($this->once())
				->method('addMessage')
				->with(OutputInterface::WARNING)
			;
		});
	}

	public function test_license_apache_2_0()
	{
		$this->validateLicense('apache-2.0', function($output)
		{
			/** @var PHPUnit\Framework\MockObject\MockObject $output */
			$output
				->expects($this->once())
				->method('addMessage')
				->with(OutputInterface::WARNING)
			;
		});
	}
}
