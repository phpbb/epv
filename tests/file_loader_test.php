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
use PHPUnit\Framework\TestCase;

class file_loader_test extends TestCase {

	/** @var FileLoader */
	private static $loader;

	public static function setUpBeforeClass(): void
	{
		require_once('./tests/Mock/Output.php');

		static::$loader = new FileLoader(new Output(), false, 'tests/testFiles/', '.');
	}

	public function test_file_php() {

		$type = static::$loader->loadFile('tests/testFiles/test.txt.php');
		$typePhp = static::$loader->loadFile('tests/testFiles/test.php');
		$typeMigration = static::$loader->loadFile('tests/testFiles/migrations/test.php');

		self::assertInstanceOf(PHPFile::class, $type);
		self::assertInstanceOf(PHPFile::class, $typePhp);
		self::assertNotInstanceOf(MigrationFile::class, $typePhp);
		self::assertNotInstanceOf(LangFile::class, $typePhp);
		self::assertInstanceOf(PHPFileInterface::class, $typeMigration); // It extends from the interface!
		self::assertInstanceOf(MigrationFile::class, $typeMigration, 'type is migration file');
	}

	public function test_file_yml()
	{
		$validYml = static::$loader->loadFile('tests/testFiles/valid.yml');
		$invalidImportYml = static::$loader->loadFile('tests/testFiles/invalid_import.yml');
		$emptyImportYml = static::$loader->loadFile('tests/testFiles/empty_import.yml');

		self::assertInstanceOf(YmlFile::class, $validYml);
		self::assertInstanceOf(YmlFile::class, $invalidImportYml);
		self::assertInstanceOf(YmlFile::class, $emptyImportYml);
	}

	public function test_file_invalid_yml()
	{
		$this->expectException(Exception::class);
		$invalidYml = static::$loader->loadFile('tests/testFiles/invalid.yml');
		self::assertNull($invalidYml);
	}

	public function test_file_empty_yml()
	{
		$this->expectException(Exception::class);
		$emptyYml = static::$loader->loadFile('tests/testFiles/empty.yml');
		self::assertNull($emptyYml);
	}
}
