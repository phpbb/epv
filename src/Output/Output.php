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

class Output implements \Phpbb\Epv\Output\OutputInterface
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

	/**
	 * Writes a message to the output.
	 *
	 * @param string|array $messages The message as an array of lines or a single string
	 * @param Boolean      $newline  Whether to add a newline
	 * @param integer      $type     The type of output (one of the OUTPUT constants)
	 *
	 * @throws \InvalidArgumentException When unknown output type is given
	 *
	 * @api
	 */
	public function write($messages, $newline = false, $type = self::OUTPUT_NORMAL)
	{
		return $this->output->write($messages, $newline, $type);
	}

	/**
	 * Writes a message to the output and adds a newline at the end.
	 *
	 * @param string|array $messages The message as an array of lines of a single string
	 * @param integer      $type     The type of output (one of the OUTPUT constants)
	 *
	 * @throws \InvalidArgumentException When unknown output type is given
	 *
	 * @api
	 */
	public function writeln($messages, $type = self::OUTPUT_NORMAL)
	{
		return $this->output->writeln($messages, $type);
	}

	/**
	 * Sets the verbosity of the output.
	 *
	 * @param integer $level The level of verbosity (one of the VERBOSITY constants)
	 *
	 * @api
	 */
	public function setVerbosity($level)
	{
		return $this->output->setVerbosity($level);
	}

	/**
	 * Gets the current verbosity of the output.
	 *
	 * @return integer The current level of verbosity (one of the VERBOSITY constants)
	 *
	 * @api
	 */
	public function getVerbosity()
	{
		return $this->output->getVerbosity();
	}

	/**
	 * Sets the decorated flag.
	 *
	 * @param Boolean $decorated Whether to decorate the messages
	 *
	 * @api
	 */
	public function setDecorated($decorated)
	{
		return $this->output->setDecorated($decorated);
	}

	/**
	 * Gets the decorated flag.
	 *
	 * @return Boolean true if the output will decorate messages, false otherwise
	 *
	 * @api
	 */
	public function isDecorated()
	{
		return $this->output->isDecorated();
	}

	/**
	 * Sets output formatter.
	 *
	 * @param OutputFormatterInterface $formatter
	 *
	 * @api
	 */
	public function setFormatter(OutputFormatterInterface $formatter)
	{
		return $this->output->setFormatter($formatter);
	}

	/**
	 * Returns current output formatter instance.
	 *
	 * @return  OutputFormatterInterface
	 *
	 * @api
	 */
	public function getFormatter()
	{
		return $this->output->getFormatter();
	}

	/**
	 * Write a message to the output, but only if Debug is enabled.
	 *
	 * @param $message string|array $messages The message as an array of lines of a single string
	 *
	 * @throws \InvalidArgumentException When unknown output type is given
	 */
	public function writelnIfDebug($message)
	{
		if ($this->debug)
		{
			$this->debugMessages[] = new Message(Output::DEBUG, $message, null);
		}
	}

	/**
	 * Add a new message to the output of the validator.
	 *
	 * @param                                $type    int message type
	 * @param                                $message string message
	 * @param \Phpbb\Epv\Files\FileInterface $file    File the error happened in. When provided, this is displayed to the user
	 */
	public function addMessage($type, $message, FileInterface $file = null)
	{
		switch ($type)
		{
			case Output::FATAL:
				$this->fatal++;
				break;
			case Output::ERROR:
				$this->error++;
				break;
			case Output::WARNING:
				$this->warning++;
				break;
			case Output::NOTICE:
				$this->notice++;
				break;
			case Output::DEBUG:
				break;
			default:
				// TODO: Decide on this?
		}
		$this->messages[] = new Message($type, $message, $file);
	}


	/**
	 * Get all messages saved into the message queue.
	 * @return array Array with messages
	 */
	public function getMessages()
	{
		return $this->messages;
	}

	/**
	 * Get all saved debug messages in the queue.
	 * @return array Array with messages
	 */
	public function getDebugMessages()
	{
		return $this->debugMessages;
	}

	/**
	 * Get the amount of messages that were fatal.
	 * @return int
	 */
	public function getFatalCount()
	{
		return $this->fatal;
	}

	/**
	 * Get the count for a type;
	 *
	 * @param $type
	 *
	 * @return mixed
	 */
	public function getMessageCount($type)
	{
		switch ($type)
		{
			case Output::FATAL:
				return $this->fatal;
			case Output::ERROR:
				return $this->error;
			case Output::WARNING:
				return $this->warning;
			case Output::NOTICE:
				return $this->notice;
		}
	}
}
