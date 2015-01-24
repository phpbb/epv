<?php
/**
 *
 * EPV :: The phpBB Forum Extension Pre Validator.
 *
 * @copyright (c) 2014 phpBB Limited <https://www.phpbb.com>
 * @license       GNU General Public License, version 2 (GPL-2.0)
 *
 */
namespace Phpbb\Epv\Tests\Tests;

use Phpbb\Epv\Output\Output;
use Phpbb\Epv\Output\OutputInterface;
use Phpbb\Epv\Tests\BaseTest;

class epv_test_validate_directory_structure extends BaseTest
{
	private $strict = false;

	public function __construct($debug, OutputInterface $output, $basedir, $namespace, $titania, $opendir)
	{
		parent::__construct($debug, $output, $basedir, $namespace, $titania, $opendir);

        $this->directory = true;
    }

	public function validateDirectory(array $dirList)
	{
		$files = array(
			'license'  => false,
			'composer' => false,
		);
		foreach ($dirList as $dir)
		{
			switch (strtolower(basename($dir)))
			{
				case 'license.txt':
					$files['license'] = true;

					if (basename($dir) != strtolower(basename($dir)))
					{
						$this->output->addMessage(Output::WARNING, 'The name of license.json should be completely lowercase.');
					}


					// Do not check license.txt location. Will give false positives in case packaging is wrong directory wise.
					break;

				case 'composer.json':
					$files['composer'] = true;

					if (basename($dir) != strtolower(basename($dir)))
					{
						$this->output->addMessage(Output::WARNING, 'The name of composer.json should be completely lowercase.');
					}
					$sp    = str_replace('\\', '/', $dir);
					$sp    = str_replace(str_replace('\\', '/', $this->opendir), '', $sp);
					$sp    = str_replace('/composer.json', '', $sp);

					if ($this->namespace != $sp)
					{
						$this->output->addMessage(Output::ERROR, 'Packaging structure doesn\'t meet the extension DB policies.');
					}
					break;
			}
		}

		if (!$files['license'])
		{
			$this->output->addMessage(Output::ERROR, 'Missing required license.txt file');
		}
	}

	public function testName()
	{
		return "Validate directory structure";
	}
}
