<?php
/**
 *
 * EPV :: The phpBB Forum Extension Pre Validator.
 *
 * @copyright (c) 2014 phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

class file_loader_test extends PHPUnit_Framework_TestCase {
	public static function setUpBeforeClass()
	{
		require_once('./tests/Mock/Output.php');
	}

	private function getLoader()
	{
		return $file = new \Phpbb\Epv\Files\FileLoader(new \Phpbb\Epv\Tests\Mock\Output(), false, 'tests/testFiles/', '.');
	}

	public function test_file_php() {
		$file = $this->getLoader();

		$type = $file->loadFile('tests/testFiles/test.txt.php');
		$typePhp = $file->loadFile('tests/testFiles/test.php');
		$typeMigration = $file->loadFile('tests/testFiles/migrations/test.php');

		$this->assertTrue($type instanceof \Phpbb\Epv\Files\Type\PHPFile);
		$this->assertTrue($typePhp instanceof \Phpbb\Epv\Files\Type\PHPFile);
		$this->assertFalse($typePhp instanceof \Phpbb\Epv\Files\Type\MigrationFile);
		$this->assertFalse($typePhp instanceof \Phpbb\Epv\Files\Type\LangFile);
		$this->assertTrue($typeMigration instanceof \Phpbb\Epv\Files\Type\PHPFileInterface); // It extends from the interface!
		$this->assertTrue($typeMigration instanceof \Phpbb\Epv\Files\Type\MigrationFile, 'type is migration file');
	}
}
