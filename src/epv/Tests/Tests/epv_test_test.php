<?php
/**
 *
 * @package EPV
 * @copyright (c) 2014 phpBB Group
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */
namespace epv\Tests\Tests;

use epv\Files\LineInterface;
use epv\Output\OutputInterface;
use epv\Tests\BaseTest;

class epv_test_test extends BaseTest{

    public function __construct($debug, OutputInterface $output, $basedir)
    {
        parent::__construct($debug, $output, $basedir);

        $this->fileTypeLine = Type::TYPE_PLAIN | Type::TYPE_SERVICE;
    }

    public function testName()
    {
        return "EPV test";
    }

    public function validateLine(LineInterface $line)
    {
    }
}
