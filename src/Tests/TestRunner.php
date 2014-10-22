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
use Phpbb\Epv\Files\FileLoader;
use Phpbb\Epv\Files\Line;
use Phpbb\Epv\Output\Output;
use Phpbb\Epv\Output\OutputInterface;
use Phpbb\Epv\Tests\Exception\TestException;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class TestRunner
{
	/** @var array */
	public $tests = array();
	/** @var array */
	private $files = array();
	/** @var array */
	private $dirList = array();

	private $output;
	private $directory;
	private $debug;
	private $basedir;
	private $namespace;
	private $titania;

	/**
	 * @param OutputInterface $output
	 * @param string          $directory The directory where the extension is located
	 * @param boolean         $debug     Debug mode
	 * @param boolean         $isTitania If we run from titania or not
	 */
	public function __construct(OutputInterface $output, $directory, $debug, $isTitania = false)
	{
		$this->output    = $output;
		$this->directory = $directory;
		$this->debug     = $debug;
		$this->titania   = $isTitania;

		$this->setBasedir();
		$this->loadTests();
		$this->loadFiles();
	}

	/**
	 * Run the actual test suite.
	 *
	 * @throws Exception\TestException
	 */
	public function runTests()
	{
		if (sizeof($this->tests) == 0)
		{
			throw new TestException("TestRunner not initialised.");
		}
		$this->output->writeln("Running tests.");

		// Now, we basicly do the same as above, but we do really run the tests.
		// All other tests are specific to files.
		/** @var  \Phpbb\Epv\Tests\TestInterface $test */
		foreach ($this->tests as $test)
		{
			if ($test->doValidateDirectory())
			{
				$test->validateDirectory($this->dirList);
			}
		}

		// Time to loop over the files in memory,
		// And over the tests that are available.
		// First do the full file check.
		// After that loop over each line and test per line.
		/** @var FileInterface $file */
		foreach ($this->files as $file)
		{
			$linetest = array();

			/** @var  \Phpbb\Epv\Tests\TestInterface $test */
			foreach ($this->tests as $test)
			{
				if ($test->doValidateFile($file->getFileType()))
				{
					$test->validateFile($file);
				}

				// To prevent looping over too many tests, we check here if we need to loop
				// over tests for line by line tests.
				if ($test->doValidateLine($file->getFileType()))
				{
					$linetest[] = $test;
				}
			}
			if (sizeof($linetest))
			{
				$linenr = 1;
				foreach ($file->getLines() as $line)
				{
					$runline = new Line($file, $linenr, $line);
					foreach ($linetest as $test)
					{
						$test->validateLine($runline);
					}
					$linenr++;
				}
			}
		}
	}

	/**
	 * Set the base directory for the extension.
	 * @throws Exception\TestException
	 */
	private function setBasedir()
	{
		$finder = new Finder();

		// First find composer.json.
		// composer.json is required, so it should always be there.
		// We use it to find the base directory of all files.
		$iterator = $finder
			->files()
			->name('composer.json')
			->exclude('vendor')
			->in($this->directory);

		if (sizeof($iterator) != 1)
		{
			throw new TestException("Can't find the required composer.json file.");
		}

		$composer = '';

		foreach ($iterator as $file)
		{
			$composer = $file;
		}

		if (empty($composer))
		{
			throw new TestException('Iterator did result a empty filename');
		}

		$this->basedir = str_replace('composer.json', '', $composer);

		$composer = @json_decode(@file_get_contents($composer));

		if (!$composer)
		{
			throw new TestException('composer.json is unreadable or invalid json');
		}
		$this->namespace = $composer->name;
	}

	/**
	 * Load all files from the extension.
	 */
	private function loadFiles()
	{
		$finder = new Finder();

		$iterator = $finder
			->ignoreDotFiles(false)
			->files()
			->sortByName()
			//->name('*')
			->ignoreVCS(true)
			->exclude('vendor')
			->exclude('tests')
			->exclude('travis')
			->in($this->directory);

		$loader = new FileLoader($this->output, $this->debug, $this->basedir, $this->directory);
		foreach ($iterator as $file)
		{
			/** @var \Symfony\Component\Finder\SplFileInfo $file */
			if (!$file->getRealPath())
			{
				$fl = $this->directory . '/' . $file->getRelativePathname();
				$this->output->write("<info>Finder found a file, but it does not seem to be readable or does not actually exist.</info>");
				continue;
			}
			$loadedFile = $loader->loadFile($file->getRealPath());

			if ($loadedFile != null)
			{
				$this->files[] = $loadedFile;

				$this->dirList[] = $file->getRealPath();
			}
			else
			{
				$this->output->addMessage(Output::FATAL, "Unable to load file: " . $file->getRealPath());
			}
		}
	}

	/**
	 * Load all available tests.
	 */
	private function loadTests()
	{
		$finder = new Finder();

		$iterator = $finder
			->files()
			->name('epv_test_*.php')
			->size(">= 0K")
			->in(__DIR__ . '/Tests');

		foreach ($iterator as $test)
		{
			$this->tryToLoadTest($test);
		}
	}

	/**
	 * Try to load and initialise a specific test.
	 *
	 * @param SplFileInfo $test
	 *
	 * @throws Exception\TestException
	 */
	private function tryToLoadTest(SplFileInfo $test)
	{
		$this->output->writelnIfDebug("<info>Found {$test->getRealpath()}.</info>");
		$file = str_replace('.php', '', basename($test->getRealPath()));

		$class = '\\Phpbb\Epv\\Tests\\Tests\\' . $file;

		$filetest = new $class($this->debug, $this->output, $this->basedir, $this->namespace, $this->titania);

		if (!$filetest instanceof TestInterface)
		{
			throw new TestException("$class does not implement the TestInterface, but matches the test expression.");
		}
		$this->tests[] = $filetest;

	}
}
