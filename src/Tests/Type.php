<?php
/**
 *
 * EPV :: The phpBB Forum Extension Pre Validator.
 *
 * @copyright (c) 2014 phpBB Limited <https://www.phpbb.com>
 * @license       GNU General Public License, version 2 (GPL-2.0)
 *
 */
namespace Phpbb\Epv\Tests;


class Type
{

	const TYPE_COMPOSER = 1;
	const TYPE_HTML = 2;
	const TYPE_LANG = 4;
	const TYPE_PHP = 8;
	const TYPE_PLAIN = 16;
	const TYPE_SERVICE = 32;
	const TYPE_XML = 64;
	const TYPE_YML = 128;
	const TYPE_JSON = 256;
	const TYPE_BINARY = 512;
	const TYPE_CSS = 1024;
	const TYPE_JS = 2048;
	const TYPE_LOCK = 4096;
	const TYPE_ROUTING = 8192;
}
