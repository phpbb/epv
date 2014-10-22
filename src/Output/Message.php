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

class Message
{
	private $type;
	private $message;
	/**
	 * @var \Phpbb\Epv\Files\FileInterface
	 */
	private $file;

	/**
	 * @param                                $type    int Type message
	 * @param                                $message string Message
	 * @param \Phpbb\Epv\Files\FileInterface $file
	 */
	public function __construct($type, $message, FileInterface $file = null)
	{
		$this->type    = $type;
		$this->message = $message;
		$this->file    = $file;
	}

	public function __toString()
	{
		$file = '';

		if ($this->file != null)
		{
			$file = ' in ' . $this->file->getSaveFilename();
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
