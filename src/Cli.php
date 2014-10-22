<?php
/**
 *
 * EPV :: The phpBB Forum Extension Pre Validator.
 *
 * @copyright (c) 2014 phpBB Limited <https://www.phpbb.com>
 * @license       GNU General Public License, version 2 (GPL-2.0)
 *
 */
namespace Phpbb\Epv;

use Phpbb\Epv\Command\ValidateCommand;
use Symfony\Component\Console\Application;

class Cli extends Application
{

	protected function getDefaultCommands()
	{
		$commands   = parent::getDefaultCommands();
		$commands[] = new ValidateCommand();

		return $commands;
	}
}
