#!/usr/bin/env php
<?php
/**
 *
 * @package EPV
 * @copyright (c) 2014 phpBB Group
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */
namespace epv;

require __DIR__ . '/../../vendor/autoload.php';

$app = new Cli();
$app->run();
