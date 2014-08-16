<?php
/**
 *
 * EPV :: The phpBB Forum Extension Pre Validator.
 *
 * @copyright (c) 2014 phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */
namespace Phpbb\Epv\Tests\Mock;


use Phpbb\Epv\Files\FileInterface;
use Phpbb\Epv\Output\OutputInterface;
use Symfony\Component\Console\Formatter\OutputFormatterInterface;

class Output implements OutputInterface
{
	public $progress = 0;
	public $messages = array();

	public function write($messages, $newline = false, $type = self::OUTPUT_NORMAL)
	{

	}

	public function writeln($messages, $type = self::OUTPUT_NORMAL)
	{

	}

	public function setVerbosity($level)
	{
	}

	public function getVerbosity()
	{
	}

	public function setDecorated($decorated)
	{

	}

	public function isDecorated()
	{

	}

	public function setFormatter(OutputFormatterInterface $formatter)
	{

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

	}

	/**
	 * Add a new message to the output of the validator.
	 *
	 * @param                                $type    int message type
	 * @param                                $message string message
	 * @param \Phpbb\Epv\Files\FileInterface $file    File the error happened in. When provided, this is displayed to the user
	 * @param bool                           $skipError
	 *
	 * @return
	 */
	public function addMessage($type, $message, FileInterface $file = null, $skipError = false)
	{
		$this->messages[] = array('type' => $type, 'message' => $message);
	}

	/**
	 * Get all messages saved into the message queue.
	 * @return array Array with messages
	 */
	public function getMessages()
	{

	}

	/**
	 * Get the amount of messages that were fatal.
	 * @return int
	 */
	public function getFatalCount()
	{

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

	}

	public function getFormatter()
	{

	}

	/**
	 * Set the max progress (Number of tests) for this run.
	 *
	 *
	 * @param $maxProgress int
	 */
	public function setMaxProgress($maxProgress)
	{

	}

	/**
	 * Increase the max progress during the run.
	 *
	 * @param $inc
	 */
	public function inMaxPogress($inc)
	{

	}

	/**
	 * Print the status of this specific test.
	 *
	 * @param $result The result for this specific test.
	 */
	public function printErrorLevel($result = null)
	{

	}
}