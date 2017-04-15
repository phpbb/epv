<?php
/**
 *
 * EPV :: The phpBB Forum Extension Pre Validator.
 *
 * @copyright (c) 2014 phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

class epv_test_validate_migrations extends PHPUnit_Framework_TestCase
{
	public static function setUpBeforeClass()
	{
		require_once('./tests/Mock/Output.php');
	}
	
    public function test_invalid_class() {
        $output = $this->getOutputMock();
        $output->expects($this->exactly(1))
            ->method('addMessage')
            ->with(\Phpbb\Epv\Output\OutputInterface::FATAL, 'Tried loading class \migrations\test from file migrations/test.php, but it does not exists in scope after including. Does your classname match the migration?')
        ;

        $this->validate($output, $this->getFile('tests/testFiles/migrations/test.php'));
    }

    public function test_no_extend_class() {
        $output = $this->getOutputMock();
        $output->expects($this->exactly(1))
            ->method('addMessage')
            ->with(\Phpbb\Epv\Output\OutputInterface::FATAL, 'Expected class \migrations\test2 to have a method depends_on, but method was not found in file migrations/test2.php.')
        ;

        $this->validate($output, $this->getFile('tests/testFiles/migrations/test2.php'));
    }

    public function test_with_wrong_return_value() {
        $output = $this->getOutputMock();
        $output->expects($this->exactly(1))
            ->method('addMessage')
            ->with(\Phpbb\Epv\Output\OutputInterface::ERROR, 'Expected that method depends_on in class \migrations\test3 returned an array in file migrations/test3.php.')
        ;

        $this->validate($output, $this->getFile('tests/testFiles/migrations/test3.php'));
    }

    public function test_with_empty_depends_class() {
        $output = $this->getOutputMock();
        $output->expects($this->exactly(0))->method('addMesssage')->withAnyParameters();

        $this->validate($output, $this->getFile('tests/testFiles/migrations/test4.php'));
    }

    public function test_with_wrong_depends_value() {
        $output = $this->getOutputMock();
        $output->expects($this->exactly(1))
            ->method('addMessage')
            ->with(\Phpbb\Epv\Output\OutputInterface::ERROR, 'The values in depends_on should start with a \ in class \migrations\test5 for dependency phpbb\error in file tests/testFiles/migrations/test5.php.')
        ;

        $this->validate($output, $this->getFile('tests/testFiles/migrations/test5.php'));
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

    /**
     * @param $fileName
     * @return \Phpbb\Epv\Files\Type\MigrationFile
     * @throws \Phpbb\Epv\Files\Exception\FileException
     */
    private function getFile($fileName)
    {
        $file = $this->getLoader()->loadFile($fileName);
        $this->assertTrue($file instanceof \Phpbb\Epv\Files\Type\MigrationFile, 'making sure we have a migration file');
        return $file;
    }

    /**
     * @param \Phpbb\Epv\Output\OutputInterface $output
     * @param \Phpbb\Epv\Files\Type\MigrationFileInterface $file
     * @throws \Phpbb\Epv\Tests\Exception\TestException
     */
    private function validate(\Phpbb\Epv\Output\OutputInterface $output, \Phpbb\Epv\Files\Type\MigrationFileInterface $file)
    {
        $tester = new \Phpbb\Epv\Tests\Tests\epv_test_validate_migrations(false, $output, '/a/b/', '', false, '/a/');

        $tester->validateFile($file);
    }
}
