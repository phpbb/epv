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

use SensioLabs\AnsiConverter\AnsiToHtmlConverter;
use Symfony\Component\Console\Formatter\OutputFormatterInterface;
use Symfony\Component\Console\Output\OutputInterface;

class HtmlOutputLegacy implements OutputInterface
{
	const TYPE_HTML = 1;
	const TYPE_BBCODE = 2;

	private $buffer = "";
	private $type;

	public function __construct($type = self::TYPE_HTML)
	{
		$this->type = $type;
	}

	public function write($messages, $newline = false, $type = self::OUTPUT_NORMAL)
	{
		if (!is_array($messages))
		{
			$messages = array($messages);
		}

		foreach ($messages as $message)
		{
			$this->buffer .= $this->parse($message);
			if ($newline)
			{
				$this->buffer .= "\n";
			}
		}
	}

	public function writeln($messages, $type = self::OUTPUT_NORMAL)
	{
		$this->write($messages, true, $type);
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

	public function getFormatter()
	{
	}

	public function getBuffer()
	{
		if ($this->type == self::TYPE_HTML)
		{
			$formatter = new OutputFormatter(true);
			$convertor = new AnsiToHtmlConverter();
			return nl2br($convertor->convert($formatter->format($this->buffer)));
		}
		return $this->buffer;
	}

	private function parse($message)
	{
		return $message;
	}

	public function isQuiet()
	{
	}

	public function isVerbose()
	{
	}

	public function isVeryVerbose()
	{
	}

	public function isDebug()
	{
	}
}