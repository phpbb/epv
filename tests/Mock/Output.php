<?php
/**
 *
 * EPV :: The phpBB Forum Extension Pre Validator.
 *
 * @copyright (c) 2014 phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */
namespace Phpbb\Epv\Tests\Mock;

// Runtime selection of Output implementation based on PHP version
if (PHP_VERSION_ID >= 80000) {
	require_once('./tests/Mock/OutputPhp8.php');
	class_alias('\Phpbb\Epv\Tests\Mock\OutputPhp8', '\Phpbb\Epv\Tests\Mock\Output');
} else {
	require_once('./tests/Mock/OutputLegacy.php');
	class_alias('\Phpbb\Epv\Tests\Mock\OutputLegacy', '\Phpbb\Epv\Tests\Mock\Output');
}
