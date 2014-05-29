<?php
/**
 *
 * @package EPV
 * @copyright (c) 2014 phpBB Group
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */
namespace Phpbb\epv\Output;

use Symfony\Component\Console\Formatter\OutputFormatterStyle;

class OutputFormatter extends \Symfony\Component\Console\Formatter\OutputFormatter
{
    public function __construct($decorated = false, array $styles = array())
    {
        parent::__construct($decorated, array_merge($styles, array(
            'success'	=> new OutputFormatterStyle('black', 'green'),
            'notice'	=> new OutputFormatterStyle('cyan'),
            'noticebg'	=> new OutputFormatterStyle('black', 'cyan'),
            'warning'	=> new OutputFormatterStyle('yellow'),
            'error'		=> new OutputFormatterStyle('red'),
            'fatal'		=> new OutputFormatterStyle('white', 'red'),
        )));
    }
}