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

use Gitonomy\Git\Admin;
use Phpbb\Epv\Output\Output;
use Phpbb\Epv\Output\OutputInterface;
use Phpbb\Epv\Tests\Exception\TestException;

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
	const TYPE_GIT = 2;
	const TYPE_GITHUB = 3;

	/**
	 * @param OutputInterface $output   Output formatter
	 * @param                 $type     int Type what the location is
	 * @param                 $location string Location where the extension is
	 * @param                 $debug    boolean if debug is enabled
	 * @param string          $branch   When using GIT and GITHUB you can provide a branch name. When empty, defaults to master
	 */
	public function __construct(OutputInterface $output, $type, $location, $debug, $branch = '')
	{
		$this->output = $output;
		$rundir       = true;

		if ($type == self::TYPE_GITHUB)
		{
			$location = 'https://github.com/' . $location;
			$type     = self::TYPE_GIT;
		}

		if ($type == self::TYPE_GIT)
		{
			$location = $this->initGit($location, $branch);
			$rundir   = false;
		}

		$this->type  = $type;
		$this->dir   = $location;
		$this->debug = $debug;

		$this->runTests($rundir);
		$this->cleanUp();
		$this->printResults();
	}

	/**
	 * Init a git repository
	 *
	 * @param string $git    Location of the git repo
	 * @param string $branch branch to checkout
	 *
	 * @throws Exception\TestException
	 * @return string local directory of the cloned repo
	 */
	private function initGit($git, $branch)
	{
		if (empty($branch))
		{
			$branch = 'master';
		}


		$this->output->writeln(sprintf("Checkout out %s from git from branch %s.", $git, $branch));
		$tmpdir = sys_get_temp_dir();
		$uniq   = $tmpdir . DIRECTORY_SEPARATOR . uniqid();

		@mkdir($uniq);

		if (!file_exists($uniq))
		{
			throw new TestException('Unable to create tmp directory');
		}

		Admin::cloneBranchTo($uniq, $git, $branch, false);

		return $uniq;
	}

	/**
	 * Run the test suite with the current directory.
	 *
	 * @param boolean $printDir print directory information
	 */
	private function runTests($printDir = true)
	{
		$dir = '';
		if ($printDir)
		{
			$dir = "on directory <info>$this->dir</info>";
		}

		$this->output->writeln("Running Extension Pre Validator{$dir}.");
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
