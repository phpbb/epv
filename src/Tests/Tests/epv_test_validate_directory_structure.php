<?php
/**
 *
 * EPV :: The phpBB Forum Extension Pre Validator.
 *
 * @copyright (c) 2014 phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */
namespace Phpbb\Epv\Tests\Tests;

use Phpbb\Epv\Output\Output;
use Phpbb\Epv\Output\OutputInterface;
use Phpbb\Epv\Tests\BaseTest;

class epv_test_validate_directory_structure  extends BaseTest{
    // $this->totalDirectoryTests is sizeof this.
    private $requiredFiles;

    public function __construct($debug, OutputInterface $output, $basedir, $namespace, $titania)
    {
        parent::__construct($debug, $output, $basedir, $namespace, $titania);

        $this->directory = true;

	    if (!$titania)
	    {
		    $ns = ''; // Skip checking full directory structure on EPV.
		    $this->output->inMaxPogress(1);
		    $this->output->addMessage(Output::NOTICE, "Important: The full directory structure is not tested. See the Extension validation guidelines for the full directory structure");
	    }
	    else
	    {
		    $ns = $namespace . '/';
	    }

	    $this->requiredFiles = array(
		    'license.txt' => Output::ERROR,
		    $ns . 'composer.json' => Output::FATAL,
		    $ns . 'ext.php' => Output::NOTICE,
	    );

        $this->totalDirectoryTests = sizeof($this->requiredFiles);
    }

    public function validateDirectory(array $dirList)
    {
        foreach ($this->requiredFiles as $file => $type)
        {
            $found = false;

            foreach ($dirList as $dir)
            {
                if (basename($dir) == $file)
                {
                    $found = true;
                    break;
                }
            }
            if (!$found)
            {
                if ($type == Output::NOTICE)
                {
                    $this->output->addMessage($type, sprintf("The suggested file %s is missing from the extension package.", $file));
                }
                else
                {
                    $this->output->addMessage($type, sprintf("The required file %s is missing from the extension package.", $file));
                }
            }
            else
            {
                $this->output->printErrorLevel();
            }
        }
    }

    public function testName()
    {
        return "Validate directory structure";
    }
}
