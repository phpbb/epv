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

	public function getVerbosity(): int
	{
	}

	public function setDecorated($decorated)
	{

	}

	public function isDecorated(): bool
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
	 *
	 * @throws \Exception
	 * @internal param bool $skipError
	 *
	 */
	public function addMessage($type, $message, FileInterface $file = null)
	{
		$this->messages[] = array('type' => $type, 'message' => $message);

		if ($type == self::FATAL) {
			throw new \Exception($message);
		}
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

	public function getFormatter(): OutputFormatterInterface
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

	/**
	 * Returns whether verbosity is quiet (-q).
	 *
	 * @return bool true if verbosity is set to VERBOSITY_QUIET, false otherwise
	 */
	public function isQuiet(): bool
	{
	}

	/**
	 * Returns whether verbosity is verbose (-v).
	 *
	 * @return bool true if verbosity is set to VERBOSITY_VERBOSE, false otherwise
	 */
	public function isVerbose(): bool
	{
	}

	/**
	 * Returns whether verbosity is very verbose (-vv).
	 *
	 * @return bool true if verbosity is set to VERBOSITY_VERY_VERBOSE, false otherwise
	 */
	public function isVeryVerbose(): bool
	{
	}

	/**
	 * Returns whether verbosity is debug (-vvv).
	 *
	 * @return bool true if verbosity is set to VERBOSITY_DEBUG, false otherwise
	 */
	public function isDebug(): bool
	{
	}
}
