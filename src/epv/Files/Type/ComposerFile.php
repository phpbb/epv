<?php
/**
 *
 * @package EPV
 * @copyright (c) 2014 phpBB Group
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */
namespace epv\Files\Type;

use epv\Tests\Tests\Type;

class ComposerFile extends JsonFile implements ComposerFileInterface{
    /**
     * Get the file type for the specific file.
     * @return int
     */
    function getFileType()
    {
        return Type::TYPE_COMPOSER | Type::TYPE_JSON;
    }
}
