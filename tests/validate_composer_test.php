<?php

use Phpbb\Epv\Files\FileLoader;
use Phpbb\Epv\Files\Type\ComposerFile;
use Phpbb\Epv\Output\OutputInterface;
use Phpbb\Epv\Tests\Mock\Output;
use Phpbb\Epv\Tests\Tests\epv_test_validate_composer;
use PHPUnit\Framework\TestCase;

/**
 *
 * EPV :: The phpBB Forum Extension Pre Validator.
 *
 * @copyright (c) 2017 phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

class validate_composer_test extends TestCase
{
	public static function setUpBeforeClass(): void
	{
		require_once('./tests/Mock/Output.php');
	}

	public function test_composer_test() {
		$output = $this->createMock(OutputInterface::class);
		$output->expects(self::atLeastOnce())
			->method('addMessage')
			->with(OutputInterface::FATAL, 'Composer validation: require.phpbb/phpbb : invalid version constraint (Could not parse version constraint <3.3.x: Invalid version string "3.3.x")')
		;

		$file_loader = new FileLoader(new Output(), false, '.', '.');
		$file = $file_loader->loadFile('tests/testFiles/composer.json');
		self::assertInstanceOf(ComposerFile::class, $file);

		$tester = new epv_test_validate_composer(false, $output, '/a/b/', 'epv/test', false, '/a/');
		$tester->validateFile($file);
	}
}
