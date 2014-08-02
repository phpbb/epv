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
                return "<noticeb>Notice:</noticeb><notice> $this->message{$file}</notice>";
            case Output::WARNING:
                return "<warningb>Warning:</warningb><warning> $this->message{$file}</warning>";
            case Output::ERROR:
                return "<errorb>Error:</errorb><error> $this->message{$file}</error>";
            case Output::FATAL:
                return "<fatalb>Fatal error:</fatalb><fatal> $this->message{$file}</fatal>";
            case Output::DEBUG:
                return $this->message;
        }
    }
}
