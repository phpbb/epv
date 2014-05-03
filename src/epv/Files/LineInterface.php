<?php
/**
 *
 * @package EPV
 * @copyright (c) 2014 phpBB Group
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */
namespace epv\Files;


interface LineInterface {
    /**
     * Get the file for this specific line
     * @return FileInterface
     */
    public function getFile();

    public function getLineNr();

    public function getLine();
} 