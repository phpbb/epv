<?php
/**
 *
 * EPV :: The phpBB Forum Extension Pre Validator.
 *
 * @copyright (c) 2014 phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

class epv_test_validate_php_functions extends PHPUnit_Framework_TestCase
{
	public static function setUpBeforeClass()
	{
		require_once('./tests/Mock/Output.php');
	}
	
    public function test_usage_of_enable_globals() {
        $output = $this->getOutputMock();
        $output->expects($this->exactly(1))
            ->method('addMessage')
            ->with(\Phpbb\Epv\Output\OutputInterface::FATAL, 'The use of enable_super_globals() is not allowed for security reasons on line 7 in enable_globals.php')
        ;

        $file = $this->getLoader()->loadFile('tests/testFiles/enable_globals.php');

        $tester = new \Phpbb\Epv\Tests\Tests\epv_test_validate_php_functions(false, $output, '/a/b/', 'epv/test', false, '/a/');
        $tester->validateFile($file);
    }

    private function getLoader()
    {
        return $file = new \Phpbb\Epv\Files\FileLoader(new \Phpbb\Epv\Tests\Mock\Output(), false, 'tests/testFiles/', 'tests/testFiles/');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    function getOutputMock()
    {
        return $this->getMock('Phpbb\Epv\Output\OutputInterface');
    }
}
