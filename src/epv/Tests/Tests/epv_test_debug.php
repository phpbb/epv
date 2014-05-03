<?php
/**
 *
 * @package EPV
 * @copyright (c) 2014 phpBB Group
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */
namespace epv\Tests\Tests;

use epv\Tests\BaseTest;

class epv_test_debug extends BaseTest{

    public function testName()
    {
        return "EPV debug";
    }

    public function isPhpLineTest()
    {
        return true;
    }
} 