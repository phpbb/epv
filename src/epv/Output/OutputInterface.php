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
     * @param $type int message type
     * @param $message string message
     * @param \epv\Files\FileInterface $file File the error happened in. When provided, this is displayed to the user
     * @param bool $skipError
     * @return
     */
    public function addMessage($type, $message, FileInterface $file = null, $skipError = false);

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
     * Set the max progress (Number of tests) for this run.
     *
     *
     * @param $maxProgress int
     */
    public function setMaxProgress($maxProgress);

    /**
     * Increase the max progress during the run.
     *
     * @param $inc
     */
    public function inMaxPogress($inc);

    /**
     * Print the status of this specific test.
     *
     * @param $result The result for this specific test.
     */
    public function printErrorLevel($result = null);
}
