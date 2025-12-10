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

// Runtime selection of HtmlOutput implementation based on PHP version and Symfony interface
if (PHP_VERSION_ID >= 80000) {
    // Check if Symfony interface has union types (Symfony 7+)
    $reflection = new \ReflectionMethod('\Symfony\Component\Console\Output\OutputInterface', 'write');
    $parameters = $reflection->getParameters();
    $hasUnionTypes = $parameters[0]->getType() && $parameters[0]->getType()->__toString() === 'string|iterable';
    
    if ($hasUnionTypes) {
        class_alias('\Phpbb\Epv\Output\HtmlOutputPhp8', '\Phpbb\Epv\Output\HtmlOutput');
    } else {
        class_alias('\Phpbb\Epv\Output\HtmlOutputLegacy', '\Phpbb\Epv\Output\HtmlOutput');
    }
} else {
    class_alias('\Phpbb\Epv\Output\HtmlOutputLegacy', '\Phpbb\Epv\Output\HtmlOutput');
}
