<?php
/**
 *
 * @package       EPV
 * @copyright (c) 2014 phpBB Group
 * @license       http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */
namespace epv\Tests;


use epv\Files\FileLoader;
use epv\Files\Line;
use epv\Output\Output;
use epv\Output\OutputInterface;
use epv\Tests\Exception\TestException;
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
	 * @param boolean         $debug      Debug mode
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

		// We start with calculating the total number of tests we are doing.
		$maxProgress = 0;

		foreach ($this->tests as $test)
		{
			if ($test->doValidateDirectory())
			{
				$maxProgress += ($test->getTotalDirectoryTests());
			}
		}

		foreach ($this->files as $file)
		{
			// Get the number of lines;
			$lines = sizeof($file->getLines());
			foreach ($this->tests as $test)
			{

				if ($test->doValidateFile($file->getFileType()))
				{
					$maxProgress += ($test->getTotalFileTests());
				}
				if ($test->doValidateLine($file->getFileType()))
				{
					$maxProgress += ($test->getTotalLineTests() * $lines);
				}
			}
		}

		$this->output->setMaxProgress($maxProgress);

		// Now, we basicly do the same as above, but we do really run the tests.
		// All other tests are specific to files.
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
		foreach ($this->files as $file)
		{
			$linetest = array();

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
			->in($this->directory);

		$loader = new FileLoader($this->output, $this->debug, $this->basedir);
		foreach ($iterator as $file)
		{
			if (!$file->getRealPath())
			{
				$fl = $this->directory . '/' . $file->getRelativePathname();
				$this->output->write("<info>Finder found a file, but it does not seem to be readable or does not actually exist.</info>");
				continue;
			}
			$loadedFile = $loader->loadFile($file->getRealPath());

			if ($loadedFile != null)
			{
				$this->files[]   = $loadedFile;
				$this->dirList[] = str_replace($this->directory, '', $file->getRealPath());
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

		$class = '\\epv\\Tests\\Tests\\' . $file;

		$filetest = new $class($this->debug, $this->output, $this->basedir, $this->namespace, $this->titania);

		if (!$filetest instanceof TestInterface)
		{
			throw new TestException("$class does not implement the TestInterface, but matches the test expression.");
		}
		$this->tests[] = $filetest;

	}
}
