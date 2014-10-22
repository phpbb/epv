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

	public function __construct($debug, OutputInterface $output, $basedir, $namespace, $titania)
	{
		parent::__construct($debug, $output, $basedir, $namespace, $titania);

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
					break;

				case 'composer.json':
					$files['composer'] = true;

					if (basename($dir) != strtolower(basename($dir)))
					{
						$this->output->addMessage(Output::WARNING, 'The name of composer.json should be completely lowercase.');
					}
					$sp    = str_replace('\\', '/', $dir);
					$split = explode('/', $sp);
					$ns    = '';
					if (sizeof($split) - 3 >= 0)
					{
						$ns .= $split[sizeof($split) - 3] . '/' . $split[sizeof($split) - 2];
					}

					if ($this->namespace != $ns)
					{
						$this->output->addMessage(Output::ERROR, 'Packaging structure doesn\'t meet the extension DB policies.');
					}
					break;
			}
		}
	}

	public function testName()
	{
		return "Validate directory structure";
	}
}
