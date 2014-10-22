<?php
/**
 *
 * EPV :: The phpBB Forum Extension Pre Validator.
 *
 * @copyright (c) 2014 phpBB Limited <https://www.phpbb.com>
 * @license       GNU General Public License, version 2 (GPL-2.0)
 *
 */
namespace Phpbb\Epv\Command;

use Phpbb\Epv\Output\Output;
use Phpbb\Epv\Output\OutputFormatter;
use Phpbb\Epv\Tests\Exception\TestException;
use Phpbb\Epv\Tests\TestStartup;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;


class ValidateCommand extends Command
{

	protected function configure()
	{
		$this
			->setName('run')
			->setDescription('Run the Extension Pre Validator on your extension.')
			//->addArgument('dir', InputArgument::OPTIONAL, 'The directory the extension is in.')
			//->addArgument('git', InputArgument::OPTIONAL, 'A git repository with the extension.')
			->addOption('dir', null, InputOption::VALUE_OPTIONAL, 'The directory the extension is in.')
			->addOption('git', null, InputOption::VALUE_OPTIONAL, 'A git repository with the extension.')
			->addOption('github', null, InputOption::VALUE_OPTIONAL, 'Shortname (like phpbb/phpbb) to github with the extension.')
			->addOption('branch', null, InputOption::VALUE_OPTIONAL, 'A branch for the git repo')
			->addOption('debug', null, InputOption::VALUE_NONE, "Run in debug");
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$dir    = $input->getOption("dir");
		$git    = $input->getOption('git');
		$github = $input->getOption('github');
		$branch = $input->getOption('branch');
		$type   = null;
		$loc    = null;

		if (!empty($github))
		{
			$type = TestStartup::TYPE_GITHUB;
			$loc  = $github;
		}
		else if (!empty($git))
		{
			$type = TestStartup::TYPE_GIT;
			$loc  = $git;
		}
		else if (!empty($dir))
		{
			$type = TestStartup::TYPE_DIRECTORY;
			$loc  = $dir;
		}
		else
		{
			throw new TestException("Or the git or the dir parameter are required");
		}

		$debug = $input->getOption("debug");

		$output = new Output($output, $debug);
		$output->setFormatter(new OutputFormatter(true));

		$test = new TestStartup($output, $type, $loc, $debug, $branch);

		if ($output->getFatalCount() > 0)
		{
			return 1;
		}

		return 0;
	}
}
