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

use Symfony\Component\Console\Formatter\OutputFormatterStyle;

class OutputFormatter extends \Symfony\Component\Console\Formatter\OutputFormatter
{
	public function __construct($decorated = false, array $styles = array())
	{
		parent::__construct($decorated, array_merge($styles, array(
			'success'   => new OutputFormatterStyle('black', 'green'),
			'notice'    => new OutputFormatterStyle('cyan'),
			'noticebg'  => new OutputFormatterStyle('black', 'cyan'),
			'warning'   => new OutputFormatterStyle('yellow'),
			'error'     => new OutputFormatterStyle('red'),
			'fatal'     => new OutputFormatterStyle('white', 'red'),

			'successb'  => new OutputFormatterStyle('black', 'green', array('bold')),
			'noticeb'   => new OutputFormatterStyle('cyan', null, array('bold')),
			'noticebgb' => new OutputFormatterStyle('black', 'cyan', array('bold')),
			'warningb'  => new OutputFormatterStyle('yellow', null, array('bold')),
			'errorb'    => new OutputFormatterStyle('red', null, array('bold')),
			'fatalb'    => new OutputFormatterStyle('white', 'red', array('bold')),

		)));
	}
}
