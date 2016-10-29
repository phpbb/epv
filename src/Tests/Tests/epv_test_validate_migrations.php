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


use Gitonomy\Git\Diff\File;
use Phpbb\Epv\Files\FileInterface;
use Phpbb\Epv\Files\Type\MigrationFileInterface;
use Phpbb\Epv\Output\OutputInterface;
use Phpbb\Epv\Tests\BaseTest;
use Phpbb\Epv\Tests\Exception\TestException;
use Phpbb\Epv\Tests\Type;

class epv_test_validate_migrations extends BaseTest
{
    /**
     * @param bool            $debug if debug is enabled
     * @param OutputInterface $output
     * @param string          $basedir
     * @param string          $namespace
     * @param boolean         $titania
     * @param string          $opendir
     */
    public function __construct($debug, OutputInterface $output, $basedir, $namespace, $titania, $opendir)
    {
        parent::__construct($debug, $output, $basedir, $namespace, $titania, $opendir);

        $this->fileTypeFull   = Type::TYPE_MIGRATION;
    }

    /**
     * @param FileInterface $file
     *
     * @throws \Phpbb\Epv\Tests\Exception\TestException
     */
    public function validateFile(FileInterface $file)
    {
        if (!$file instanceof MigrationFileInterface)
        {
            throw new TestException('This test expects a migration type, but found something else.');
        }
        $this->validate($file);
    }

    /**
     * @param FileInterface $file
     */
    private function validate(FileInterface $file)
    {
        $classname = $file->getSaveFilename();
        $classname = str_replace('/', '\\', $classname);
        $classname = str_replace('.php', '', $classname);
        $classname = '\\' . $classname;

        if (class_exists($classname))
        {
            // Should not happen.
            // All migrations should be unique in class/filename.
            $this->output->addMessage(OutputInterface::FATAL, sprintf('Tried loading class %s from file %s, but it already exists in scope. Does your classname match the migration?', $classname, $file->getSaveFilename()));
            return;
        }

        @include($file->getFilename());

        if (!class_exists($classname))
        {
            $this->output->addMessage(OutputInterface::FATAL, sprintf('Tried loading class %s from file %s, but it does not exists in scope after including. Does your classname match the migration?', $classname, $file->getSaveFilename()));
            return;
        }

        if (!method_exists($classname, 'depends_on'))
        {
            $this->output->addMessage(OutputInterface::FATAL, sprintf('Expected class %s to have a method depends_on, but method was not found in file %s.', $classname, $file->getSaveFilename()));
            return;
        }

        $list = $classname::depends_on();

        if (!is_array($list))
        {
            $this->output->addMessage(OutputInterface::ERROR, sprintf('Expected that method depends_on in class %s returned an array in file %s.', $classname, $file->getSaveFilename()));
            return;
        }

        foreach($list as $row)
        {
            if ($row[0] !== '\\')
            {
                $this->output->addMessage(OutputInterface::ERROR, sprintf('The values in depends_on should start with a \\ in class %s for dependency %s in file %s.', $classname, $row, $file->getFilename()));
            }
        }

    }

    /**
     *
     * @return string
     */
    public function testName()
    {
        return "Validate migration files";
    }
}