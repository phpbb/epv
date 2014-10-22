<?php
/**
 *
 * EPV :: The phpBB Forum Extension Pre Validator.
 *
 * @copyright (c) 2014 phpBB Limited <https://www.phpbb.com>
 * @license       GNU General Public License, version 2 (GPL-2.0)
 *
 */
namespace Phpbb\Epv\Tests;


use Phpbb\Epv\Files\FileInterface;
use Phpbb\Epv\Files\LineInterface;
use Phpbb\Epv\Output\OutputInterface;
use Phpbb\Epv\Tests\Exception\TestException;

abstract class BaseTest implements TestInterface
{
	private $debug;
	protected $fileTypeLine;
	protected $fileTypeFull;

	// Current file. Used in some tests.
	/** @var  \Phpbb\Epv\Files\FileInterface * */
	protected $file;

	/**
	 * If this is set to true, tests are run on full directory listings.
	 * @var bool
	 */
	protected $directory = false;
	/**
	 * @var \Phpbb\Epv\Output\OutputInterface
	 */
	protected $output;
	/**
	 * @var
	 */
	protected $basedir;

	/**
	 * @var string
	 */
	protected $namespace;

	/**
	 * @var boolean
	 */
	protected $titania;

	/**
	 * @param                                   $debug
	 * @param \Phpbb\Epv\Output\OutputInterface $output
	 * @param                                   $basedir   string Basedirectory of the extension
	 * @param                                   $namespace string Namespace of the extension
	 * @param                                   $titania
	 */
	public function __construct($debug, OutputInterface $output, $basedir, $namespace, $titania)
	{
		$this->debug     = $debug;
		$this->output    = $output;
		$this->basedir   = $basedir;
		$this->namespace = $namespace;
		$this->titania   = $titania;
	}

	/**
	 *
	 * @param \Phpbb\Epv\Files\LineInterface $line
	 *
	 * @throws Exception\TestException
	 * @internal param $
	 */
	public function validateLine(LineInterface $line)
	{
		throw new TestException("Test declared to be a line test, but doesn't implement validateLine.");
	}

	/**
	 * @param \Phpbb\Epv\Files\FileInterface $file
	 *
	 * @throws Exception\TestException
	 * @internal param $
	 */
	public function validateFile(FileInterface $file)
	{
		throw new TestException("Test declared to be a file test, but doesn't implement validateFile.");
	}

	/**
	 * @param array $dirList
	 *
	 * @return mixed|void
	 * @throws Exception\TestException
	 */
	public function validateDirectory(array $dirList)
	{
		throw new TestException("Test declared to be a directory listing test, but doesn't implement validateDirectory.");
	}

	/**
	 * @param int $type
	 *
	 * @return bool
	 */
	public function doValidateLine($type)
	{
		return $this->fileTypeLine & $type;
	}

	/**
	 * @param int $type
	 *
	 * @return bool
	 */
	public function doValidateFile($type)
	{
		return $this->fileTypeFull & $type;
	}

	/**
	 * @return bool
	 */
	public function doValidateDirectory()
	{
		return $this->directory;
	}

	/**
	 * Convert a boolean to Yes or No.
	 *
	 * @param $bool
	 *
	 * @return string
	 */
	private function boolToLang($bool)
	{
		return $bool ? "Yes" : "No";
	}

	/**
	 * @return string
	 */
	public function __toString()
	{
		$string = 'Test: ' . $this->testName() . '. ';

		return $string;
	}

	/**
	 * Checks to see if the current file is for tests.
	 *
	 * @param FileInterface $file
	 *
	 * @return bool
	 */
	protected function isTest(FileInterface $file = null)
	{
		if ($file == null)
		{
			$file = $this->file;
		}

		$dir = str_replace($this->basedir, '', $file->getFilename());
		$dir = explode("/", $dir);

		return $dir[0] == 'test' || $dir[0] == 'tests';
	}
}
