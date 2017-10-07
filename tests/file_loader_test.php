<?php
/**
 *
 * EPV :: The phpBB Forum Extension Pre Validator.
 *
 * @copyright (c) 2014 phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

use Phpbb\Epv\Files\FileLoader;
use Phpbb\Epv\Files\Type\LangFile;
use Phpbb\Epv\Files\Type\MigrationFile;
use Phpbb\Epv\Files\Type\PHPFile;
use Phpbb\Epv\Files\Type\PHPFileInterface;
use Phpbb\Epv\Files\Type\YmlFile;
use Phpbb\Epv\Tests\Mock\Output;

class file_loader_test extends PHPUnit_Framework_TestCase {

	/** @var FileLoader */
	private static $loader;

	public static function setUpBeforeClass()
	{
		require_once('./tests/Mock/Output.php');

		static::$loader = new FileLoader(new Output(), false, 'tests/testFiles/', '.');
	}

	public function test_file_php() {

		$type = static::$loader->loadFile('tests/testFiles/test.txt.php');
		$typePhp = static::$loader->loadFile('tests/testFiles/test.php');
		$typeMigration = static::$loader->loadFile('tests/testFiles/migrations/test.php');

		$this->assertTrue($type instanceof PHPFile);
		$this->assertTrue($typePhp instanceof PHPFile);
		$this->assertFalse($typePhp instanceof MigrationFile);
		$this->assertFalse($typePhp instanceof LangFile);
		$this->assertTrue($typeMigration instanceof PHPFileInterface); // It extends from the interface!
		$this->assertTrue($typeMigration instanceof MigrationFile, 'type is migration file');
	}

	public function test_file_yml()
	{
		$validYml = static::$loader->loadFile('tests/testFiles/valid.yml');
		$invalidImportYml = static::$loader->loadFile('tests/testFiles/invalid_import.yml');
		$emptyImportYml = static::$loader->loadFile('tests/testFiles/empty_import.yml');

		$this->assertTrue($validYml instanceof YmlFile);
		$this->assertTrue($invalidImportYml instanceof YmlFile);
		$this->assertTrue($emptyImportYml instanceof YmlFile);
	}

	public function test_file_invalid_yml()
	{
		$this->setExpectedException(Exception::class);
		$invalidYml = static::$loader->loadFile('tests/testFiles/invalid.yml');
		$this->assertNull($invalidYml);
	}

	public function test_file_empty_yml()
	{
		$this->setExpectedException(Exception::class);
		$emptyYml = static::$loader->loadFile('tests/testFiles/empty.yml');
		$this->assertNull($emptyYml);
	}
}
