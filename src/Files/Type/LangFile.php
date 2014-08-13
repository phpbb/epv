<?php
/**
 *
 * @package EPV
 * @copyright (c) 2014 phpBB Group
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */
namespace Phpbb\Epv\Files\Type;

use Phpbb\Epv\Files\BaseFile;
use Phpbb\Epv\Tests\Type;

class LangFile extends BaseFile implements LangFileInterface{
    /**
     * Get the file type for the specific file.
     * @return int
     */
    function getFileType()
    {
        return Type::TYPE_LANG | Type::TYPE_PHP;
    }
}
