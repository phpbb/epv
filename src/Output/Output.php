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

// Runtime selection of Output implementation based on PHP version
if (PHP_VERSION_ID >= 80000) {
    class_alias('\Phpbb\Epv\Output\OutputPhp8', '\Phpbb\Epv\Output\Output');
} else {
    class_alias('\Phpbb\Epv\Output\OutputLegacy', '\Phpbb\Epv\Output\Output');
}
