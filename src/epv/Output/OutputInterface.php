<?php
/**
 *
 * @package EPV
 * @copyright (c) 2014 phpBB Group
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */
namespace epv\Output;


use epv\Files\FileInterface;

interface OutputInterface extends \Symfony\Component\Console\Output\OutputInterface {
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
     * @param $type int message type
     * @param $message string message
     * @param \epv\Files\FileInterface $file
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
}
