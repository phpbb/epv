<?php
/**
 *
 * EPV :: The phpBB Forum Extension Pre Validator.
 *
 * @copyright (c) 2014 phpBB Limited <https://www.phpbb.com>
 * @license       GNU General Public License, version 2 (GPL-2.0)
 *
 */
namespace Phpbb\Epv\Output;

use Phpbb\Epv\Files\FileInterface;
use Symfony\Component\Console\Formatter\OutputFormatterInterface;

class OutputPhp8 implements \Phpbb\Epv\Output\OutputInterface
{
	private $messages = array();
	private $debugMessages = array();
	private $fatal = 0;
	private $error = 0;
	private $warning = 0;
	private $notice = 0;

	private $output;
	private $debug;

	/** @var int */
	protected $progress = 0;
	/** @var int */
	protected $maxProgress = 0;

	public function __construct(\Symfony\Component\Console\Output\OutputInterface $output, $debug)
	{
		$this->output = $output;
		$this->debug  = $debug;
	}

	public function write(string|iterable $messages, bool $newline = false, int $options = self::OUTPUT_NORMAL): void
	{
		$this->output->write($messages, $newline, $options);
	}

	public function writeln(string|iterable $messages, int $options = self::OUTPUT_NORMAL): void
	{
		$this->output->writeln($messages, $options);
	}

	public function setVerbosity(int $level): void
	{
		$this->output->setVerbosity($level);
	}

	public function getVerbosity(): int
	{
		return $this->output->getVerbosity();
	}

	public function setDecorated(bool $decorated): void
	{
		$this->output->setDecorated($decorated);
	}

	public function isDecorated(): bool
	{
		return $this->output->isDecorated();
	}

	public function setFormatter(OutputFormatterInterface $formatter): void
	{
		$this->output->setFormatter($formatter);
	}

	public function getFormatter(): OutputFormatterInterface
	{
		return $this->output->getFormatter();
	}

	public function writelnIfDebug($message)
	{
		if ($this->debug)
		{
			$this->debugMessages[] = new Message(OutputInterface::DEBUG, $message, null);
		}
	}

	public function addMessage($type, $message, ?FileInterface $file = null)
	{
		switch ($type)
		{
			case OutputInterface::FATAL:
				$this->fatal++;
				break;
			case OutputInterface::ERROR:
				$this->error++;
				break;
			case OutputInterface::WARNING:
				$this->warning++;
				break;
			case OutputInterface::NOTICE:
				$this->notice++;
				break;
			case OutputInterface::DEBUG:
				break;
		}
		$this->messages[] = new Message($type, $message, $file);
	}

	public function getMessages()
	{
		return $this->messages;
	}

	public function getDebugMessages()
	{
		return $this->debugMessages;
	}

	public function getFatalCount()
	{
		return $this->fatal;
	}

	public function getMessageCount($type)
	{
		switch ($type)
		{
			case OutputInterface::FATAL:
				return $this->fatal;
			case OutputInterface::ERROR:
				return $this->error;
			case OutputInterface::WARNING:
				return $this->warning;
			case OutputInterface::NOTICE:
				return $this->notice;
		}
	}

	public function isQuiet(): bool
	{
		return $this->output->isQuiet();
	}

	public function isVerbose(): bool
	{
		return $this->output->isVerbose();
	}

	public function isVeryVerbose(): bool
	{
		return $this->output->isVeryVerbose();
	}

	public function isDebug(): bool
	{
		return $this->output->isDebug();
	}
}