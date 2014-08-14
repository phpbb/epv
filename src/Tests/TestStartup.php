<?php

namespace Phpbb\Epv\Tests;

use Phpbb\Epv\Output\Output;
use Phpbb\Epv\Output\OutputInterface;
use Phpbb\Epv\Tests\Exception\TestException;
use Gitonomy\Git\Admin;

class TestStartup
{
	/** @var string */
	private $dir = null;
	/** @var bool */
	private $debug = false;

	/** @var int|null */
	private $type = null;

	/** @var \Phpbb\Epv\Output\Output */
	private $output;

	const TYPE_DIRECTORY = 1;
	const TYPE_GIT       = 2;
	const TYPE_GITHUB    = 3;

	/**
	 * @param OutputInterface $output   Output formatter
	 * @param                 $type     int Type what the location is
	 * @param                 $location string Location where the extension is
	 * @param                 $debug    boolean if debug is enabled
	 */
	public function __construct(OutputInterface $output, $type, $location, $debug)
	{
		$this->output = $output;

		if ($type == self::TYPE_GITHUB)
		{
			$location = 'https://github.com/' . $location;
			$type     = self::TYPE_GIT;
		}

		if ($type == self::TYPE_GIT)
		{
			$location = $this->initGit($location);
		}
		$this->type  = $type;
		$this->dir   = $location;
		$this->debug = $debug;

		$this->runTests();
		$this->cleanUp();
		$this->printResults();
	}

	/**
	 * Init a git repository
	 *
	 * @param $git string Location of the git repo
	 *
	 * @return string local directory of the cloned repo
	 * @throws Exception\TestException
	 */
	private function initGit($git)
	{
		$this->output->writeln(sprintf("Checkout out %s from git", $git));
		$tmpdir = sys_get_temp_dir();
		$uniq   = $tmpdir . DIRECTORY_SEPARATOR . uniqid();

		@mkdir($uniq);

		if (!file_exists($uniq))
		{
			throw new TestException('Unable to create tmp directory');
		}

		Admin::cloneTo($uniq, $git, false);

		return $uniq;
	}

	/**
	 * Run the test suite with the current directory.
	 */
	private function runTests()
	{
		$this->output->writeln("Running Extension Pre Validator on directory <info>$this->dir</info>.");
		$runner = new TestRunner($this->output, $this->dir, $this->debug);

		if ($this->debug)
		{
			$this->output->writelnIfDebug("tests to run:");

			foreach ($runner->tests as $t => $test)
			{
				$this->output->writelnIfDebug("<info>$test</info>");
			}
		}
		$runner->runTests();
	}

	/**
	 * Print the results from the tests
	 */
	private function printResults()
	{
		// Write a empty line
		$this->output->writeLn('');

		$found_msg = ' ';
		$found_msg .= 'Fatal: ' . $this->output->getMessageCount(Output::FATAL);
		$found_msg .= ', Error: ' . $this->output->getMessageCount(Output::ERROR);
		$found_msg .= ', Warning: ' . $this->output->getMessageCount(Output::WARNING);
		$found_msg .= ', Notice: ' . $this->output->getMessageCount(Output::NOTICE);
		$found_msg .= ' ';

		if ($this->output->getMessageCount(Output::FATAL) > 0 || $this->output->getMessageCount(Output::ERROR) > 0 || $this->output->getMessageCount(Output::WARNING) > 0)
		{
			$this->output->writeln('<fatal>' . str_repeat(' ', strlen($found_msg)) . '</fatal>');
			$this->output->writeln('<fatal> Validation: FAILED' . str_repeat(' ', strlen($found_msg) - 19) . '</fatal>');
			$this->output->writeln('<fatal>' . $found_msg . '</fatal>');
			$this->output->writeln('<fatal>' . str_repeat(' ', strlen($found_msg)) . '</fatal>');
			$this->output->writeln('');
		}
		else
		{
			$this->output->writeln('<success>PASSED: ' . $found_msg . '</success>');
		}

		// Write debug messages.
		if ($this->debug)
		{
			foreach ($this->output->getDebugMessages() as $msg)
			{
				$this->output->writeln((string)$msg);
			}
		}

		$this->output->writeln("<info>Test results for extension:</info>");

		foreach ($this->output->getMessages() as $msg)
		{
			$this->output->writeln((string)$msg);
		}

		if (sizeof($this->output->getMessages()) == 0)
		{
			$this->output->writeln("<success>No issues found </success>");
		}
	}

	/**
	 * Cleanup the mess we made
	 */
	private function cleanUp()
	{
		if ($this->type == self::TYPE_GIT)
		{
			$this->rrmdir($this->dir);
		}
	}

	/**
	 * Remove a directory including the contents
	 *
	 * @param $dir string Directory to remove
	 */
	private function rrmdir($dir)
	{
		if (is_dir($dir))
		{
			$objects = scandir($dir);

			foreach ($objects as $object)
			{
				if ($object != "." && $object != "..")
				{
					if (filetype($dir . "/" . $object) == "dir")
					{
						$this->rrmdir($dir . "/" . $object);
					}
					else
					{
						@unlink($dir . "/" . $object);
					}
				}
			}
			reset($objects);
			rmdir($dir);
		}
	}
}
