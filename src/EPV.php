#!/usr/bin/env php
<?php
/**
 *
 * EPV :: The phpBB Forum Extension Pre Validator.
 *
 * @copyright (c) 2014 phpBB Limited <https://www.phpbb.com>
 * @license       GNU General Public License, version 2 (GPL-2.0)
 *
 */

if (file_exists(__DIR__ . '/../vendor/autoload.php'))
{
	require __DIR__ . '/../vendor/autoload.php';
}
else if (file_exists(__DIR__ . '/../../../vendor/autoload.php'))
{
	require __DIR__ . '/../../../vendor/autoload.php';
}
else if (file_exists(__DIR__ . '/../../../../vendor/autoload.php'))
{
	require __DIR__ . '/../../../../vendor/autoload.php';
}
else
{
	exit('Composer autoloading seems to be missing. Did you run composer.phar install?');
}

$app = new Phpbb\Epv\Cli();
$app->run();
