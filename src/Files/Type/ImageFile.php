<?php
/**
 *
 * @package EPV
 * @copyright (c) 2014 phpBB Group
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */
namespace Phpbb\Epv\Files\Type;

use Phpbb\Epv\Tests\Type;
use Phpbb\Epv\Files\BaseFile;

class ImageFile extends BaseFile implements ImageFileInterface{
    /**
     * Get the file type for the specific file.
     * @todo Do we need a TYPE for images?
     * @return int
     */
    function getFileType()
    {
        return Type::TYPE_BINARY;
    }
}
