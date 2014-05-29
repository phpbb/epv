<?php
/**
 *
 * @package EPV
 * @copyright (c) 2014 phpBB Group
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */
namespace epv\Command;

use epv\Output\Output;
use epv\Tests\TestRunner;
use Phpbb\epv\Output\OutputFormatter;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ValidateCommand extends  Command{
    protected $debug;
    protected function configure()
    {
        $this
            ->setName('run')
            ->setDescription('Run the Extension Pre Validator on your extension.')
            ->addArgument('dir', InputArgument::REQUIRED, 'The directory the extension is in.')
            ->addOption('debug', null, InputOption::VALUE_NONE, "Run in debug")

        ;
    }
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dir = $input->getArgument("dir");
        $this->debug = $input->getOption("debug");

        $output = new Output($output, $this->debug);
        $output->setFormatter(new OutputFormatter(true));

        $output->writeln("Running Extension Pre Validator on directory <info>$dir</info>.");
        $runner = new TestRunner($input, $output, $dir, $this->debug);

        if ($this->debug)
        {
            $output->writelnIfDebug("tests to run:");

            foreach ($runner->tests as $t => $test)
            {
                $output->writelnIfDebug("<info>$test</info>");
            }
        }
        $runner->runTests();

        // Write a empty line
        $output->writeLn('');

        $found_msg = '';
        $found_msg .= 'Fatal: ' . $output->getMessageCount(Output::FATAL);
        $found_msg .= ', Error: ' . $output->getMessageCount(Output::ERROR);
        $found_msg .= ', Warning: ' . $output->getMessageCount(Output::WARNING);
        $found_msg .= ', Notice: ' . $output->getMessageCount(Output::NOTICE);

        if ($output->getMessageCount(Output::FATAL))
        {
            $output->writeln('<fatal>' . str_repeat(' ', strlen($found_msg)) . '</fatal>');
            $output->writeln('<fatal>Validation: FAILED' . str_repeat(' ', strlen($found_msg) - 18) . '</fatal>');
            $output->writeln('<fatal>' . $found_msg .  '</fatal>');
            $output->writeln('');
            $output->writeln('');
        }
        else
        {
            $output->writeln('<success>PASSED: ' . $found_msg . '</success>');
        }

        // Write debug messages.
        if ($this->debug)
        {
            foreach ($output->getDebugMessages() as $msg)
            {
                $output->writeln((string)$msg);
            }
        }

        $output->writeln("<info>Test results for extension:</info>");

        foreach ($output->getMessages() as $msg)
        {
            $output->writeln((string)$msg);
        }

        if (sizeof($output->getMessages()) == 0)
        {
            $output->writeln("<success>No issues found </success>");
        }

        if ($output->getFatalCount() > 0)
        {
            return 1;
        }
        return 0;
    }
}
