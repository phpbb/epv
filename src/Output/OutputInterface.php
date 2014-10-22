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

interface OutputInterface extends \Symfony\Component\Console\Output\OutputInterface
{
	const DEBUG = 5;
	const FATAL = 4;
	const WARNING = 3;
	const ERROR = 2;
	const NOTICE = 1;

	/**
	 * Write a message to the output, but only if Debug is enabled.
	 *
	 * @param $message string|array $messages The message as an array of lines of a single string
	 *
	 * @throws \InvalidArgumentException When unknown output type is given
	 */
	public function writelnIfDebug($message);

	/**
	 * Add a new message to the output of the validator.
	 *
	 * @param                                $type    int message type
	 * @param                                $message string message
	 * @param \Phpbb\Epv\Files\FileInterface $file    File the error happened in. When provided, this is displayed to the user
	 *
	 * @return
	 */
	public function addMessage($type, $message, FileInterface $file = null);


	/**
	 * Get all messages saved into the message queue.
	 * @return array Array with messages
	 */
	public function getMessages();

	/**
	 * Get the amount of messages that were fatal.
	 * @return int
	 */
	public function getFatalCount();

	/**
	 * Get the count for a type;
	 *
	 * @param $type
	 *
	 * @return mixed
	 */
	public function getMessageCount($type);

}
