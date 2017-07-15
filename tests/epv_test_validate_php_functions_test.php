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
            ->with(\Phpbb\Epv\Output\OutputInterface::FATAL, 'The use of enable_super_globals() is not allowed for security reasons on line 7 in tests/testFiles/enable_globals.php')
        ;

        $file = $this->getLoader()->loadFile('tests/testFiles/enable_globals.php');

        $tester = new \Phpbb\Epv\Tests\Tests\epv_test_validate_php_functions(false, $output, '/a/b/', 'epv/test', false, '/a/');
        $tester->validateFile($file);
    }

    public function test_usage_of_enable_globals2() {
        $output = $this->getOutputMock();
        $output->expects($this->exactly(0))
            ->method('addMessage')
             ;

        $file = $this->getLoader()->loadFile('tests/testFiles/enable_globals2.php');

        $tester = new \Phpbb\Epv\Tests\Tests\epv_test_validate_php_functions(false, $output, '/a/b/', 'epv/test', false, '/a/');
        $tester->validateFile($file);
    }

    public function test_usage_of_enable_globals3() {
        $output = $this->getOutputMock();
        $output->expects($this->exactly(1))
            ->method('addMessage')
            ->with(\Phpbb\Epv\Output\OutputInterface::FATAL, 'The use of enable_super_globals() is not allowed for security reasons on line 7 in tests/testFiles/enable_globals3.php')
        ;

        $file = $this->getLoader()->loadFile('tests/testFiles/enable_globals3.php');

        $tester = new \Phpbb\Epv\Tests\Tests\epv_test_validate_php_functions(false, $output, '/a/b/', 'epv/test', false, '/a/');
        $tester->validateFile($file);
    }

    public function test_usage_of_addslashes() {
        $output = $this->getOutputMock();
        $output->expects($this->exactly(1))
            ->method('addMessage')
            ->with(\Phpbb\Epv\Output\OutputInterface::ERROR, 'Using addslashes on line 8 in tests/testFiles/addslashes.php')
        ;

        $file = $this->getLoader()->loadFile('tests/testFiles/addslashes.php');

        $tester = new \Phpbb\Epv\Tests\Tests\epv_test_validate_php_functions(false, $output, '/a/b/', 'epv/test', false, '/a/');
        $tester->validateFile($file);
    }

    public function test_usage_of_evals() {
        $output = $this->getOutputMock();
        $output->expects($this->exactly(1))
            ->method('addMessage')
            ->with(\Phpbb\Epv\Output\OutputInterface::FATAL, 'The use of eval() is not allowed for security reasons on line 8 in tests/testFiles/eval.php')
        ;

        $file = $this->getLoader()->loadFile('tests/testFiles/eval.php');

        $tester = new \Phpbb\Epv\Tests\Tests\epv_test_validate_php_functions(false, $output, '/a/b/', 'epv/test', false, '/a/');
        $tester->validateFile($file);
    }

    public function test_usage_of_no_inphpbb() {
        $output = $this->getOutputMock();
        $output->expects($this->exactly(1))
            ->method('addMessage')
            ->with(\Phpbb\Epv\Output\OutputInterface::WARNING, 'IN_PHPBB is not defined in tests/testFiles/no_in_phpbb.php')
        ;

        $file = $this->getLoader()->loadFile('tests/testFiles/no_in_phpbb.php');

        $tester = new \Phpbb\Epv\Tests\Tests\epv_test_validate_php_functions(false, $output, '/a/b/', 'epv/test', false, '/a/');
        $tester->validateFile($file);
    }

    public function test_usage_of_wrong_in_phpbb() {
        $output = $this->getOutputMock();
        $output->expects($this->exactly(2))
            ->method('addMessage')
        ;

        $file = $this->getLoader()->loadFile('tests/testFiles/in_phpbb_wrong.php');

        $tester = new \Phpbb\Epv\Tests\Tests\epv_test_validate_php_functions(false, $output, '/a/b/', 'epv/test', false, '/a/');
        $tester->validateFile($file);
    }

    public function test_usage_of_wrong_in_phpbb2() {
        $output = $this->getOutputMock();
        $output->expects($this->exactly(5))
            ->method('addMessage')
        ;

        $file = $this->getLoader()->loadFile('tests/testFiles/in_phpbb_wrong2.php');

        $tester = new \Phpbb\Epv\Tests\Tests\epv_test_validate_php_functions(false, $output, '/a/b/', 'epv/test', false, '/a/');
        $tester->validateFile($file);
    }

    public function test_usage_of_namespace() {
        $output = $this->getOutputMock();
        $output->expects($this->exactly(2))
            ->method('addMessage')
        ;

        $file = $this->getLoader()->loadFile('tests/testFiles/no_namespace.php');

        $tester = new \Phpbb\Epv\Tests\Tests\epv_test_validate_php_functions(false, $output, '/a/b/', 'epv/test', false, '/a/');
        $tester->validateFile($file);
    }

    public function test_usage_of_var_test() {
        $output = $this->getOutputMock();
        $output->expects($this->exactly(0))
            ->method('addMessage')
        ;

        $file = $this->getLoader()->loadFile('tests/testFiles/var_test.php');

        $tester = new \Phpbb\Epv\Tests\Tests\epv_test_validate_php_functions(false, $output, '/a/b/', 'epv/test', false, '/a/');
        $tester->validateFile($file);
    }

    private function getLoader()
    {
        return $file = new \Phpbb\Epv\Files\FileLoader(new \Phpbb\Epv\Tests\Mock\Output(), false, '.', '.');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    function getOutputMock()
    {
        return $this->getMock('Phpbb\Epv\Output\OutputInterface');
    }
}
