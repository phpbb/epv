<?php
/**
 *
 * @package EPV
 * @copyright (c) 2014 phpBB Group
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */
namespace Phpbb\Epv\Files;


interface FileInterface {
    /**
     * Get the file type for a specific file.
     * @return int
     */
    function getFileType();

    /**
     * Get an array of lines for a specific file.
     * @return array
     */
    function getLines();

    /**
     * Get the filename for a file.
     * @return string
     */
    function getFilename();

    /**
     * Get the full file for a specific file.
     * @return string
     */
    function getFile();
}
