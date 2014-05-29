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

class Message {
    private $type;
    private $message;
    /**
     * @var \epv\Files\FileInterface
     */
    private $file;

    /**
     * @param $type int Type message
     * @param $message string Message
     * @param \epv\Files\FileInterface $file
     */
    public function __construct($type, $message, FileInterface $file = null)
    {
        $this->type = $type;
        $this->message = $message;
        $this->file = $file;
    }

    public function __toString()
    {
        $file = '';

        if ($this->file != null)
        {
            $file = ' in ' . $this->file->getFilename();
        }

        switch ($this->type)
        {
            case Output::NOTICE:
                return "<info>Notice{$file}: $this->message</info>";
            case Output::WARNING:
                return "<comment>Warning{$file}: $this->message</comment>";
            case Output::ERROR:
                return "<question>Error{$file}: $this->message</question>";
            case Output::FATAL:
                return "<error>Fatal{$file}: $this->message</error>";
        }
    }
}
