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

class HtmlOutput implements OutputInterface
{
	const TYPE_HTML = 1;
	const TYPE_BBCODE = 2;

	private $buffer = "";
	private $type;

	/**
	 * @param int $type Output type (HTML or BBCode)
	 */
	public function __construct($type = self::TYPE_HTML)
	{
		$this->type = $type;
	}

	/**
	 * Writes a message to the output.
	 *
	 * @param string|array $messages The message as an array of lines or a single string
	 * @param bool         $newline  Whether to add a newline
	 * @param int          $type     The type of output (one of the OUTPUT constants)
	 *
	 * @throws \InvalidArgumentException When unknown output type is given
	 *
	 * @api
	 */
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

	/**
	 * Writes a message to the output and adds a newline at the end.
	 *
	 * @param string|array $messages The message as an array of lines of a single string
	 * @param int          $type     The type of output (one of the OUTPUT constants)
	 *
	 * @throws \InvalidArgumentException When unknown output type is given
	 *
	 * @api
	 */
	public function writeln($messages, $type = self::OUTPUT_NORMAL)
	{
		$this->write($messages, true, $type);
	}

	/**
	 * Sets the verbosity of the output.
	 *
	 * @param int $level The level of verbosity (one of the VERBOSITY constants)
	 *
	 * @api
	 */
	public function setVerbosity($level)
	{

	}

	/**
	 * Gets the current verbosity of the output.
	 *
	 * @return int     The current level of verbosity (one of the VERBOSITY constants)
	 *
	 * @api
	 */
	public function getVerbosity()
	{

	}

	/**
	 * Sets the decorated flag.
	 *
	 * @param bool $decorated Whether to decorate the messages
	 *
	 * @api
	 */
	public function setDecorated($decorated)
	{

	}

	/**
	 * Gets the decorated flag.
	 *
	 * @return bool    true if the output will decorate messages, false otherwise
	 *
	 * @api
	 */
	public function isDecorated()
	{

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

	/**
	 * Parse the code from the CLI to html.
	 *
	 * @param $message
	 *
	 * @return mixed Parsed message
	 */
	private function parse($message)
	{
		return $message;
	}
}
