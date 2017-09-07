<?php
/**
 *
 * EPV :: The phpBB Forum Extension Pre Validator.
 *
 * @copyright (c) 2017 phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

class validate_composer_test extends PHPUnit_Framework_TestCase
{
	public static function setUpBeforeClass()
	{
		require_once('./tests/Mock/Output.php');
	}

	public function test_composer_test() {
		$output = $this->getMock('Phpbb\Epv\Output\OutputInterface');
		$output->expects($this->atLeastOnce())
			->method('addMessage')
			->with(\Phpbb\Epv\Output\OutputInterface::FATAL, 'Composer validation: require.phpbb/phpbb : invalid version constraint (Could not parse version constraint <3.3.x: Invalid version string "3.3.x")')
		;

		$file_loader = new \Phpbb\Epv\Files\FileLoader(new \Phpbb\Epv\Tests\Mock\Output(), false, '.', '.');
		$file = $file_loader->loadFile('tests/testFiles/composer.json');
		$this->assertTrue($file instanceof \Phpbb\Epv\Files\Type\ComposerFile);

		$tester = new \Phpbb\Epv\Tests\Tests\epv_test_validate_composer(false, $output, '/a/b/', 'epv/test', false, '/a/');
		$tester->validateFile($file);

	}
}
