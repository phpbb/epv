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

class JsonFile extends BaseFile implements JsonFileInterface{
    /** @var array  */
    private $json;

    public function __construct($debug, $filename)
    {
        parent::__construct($debug, $filename);
        $this->json = json_decode($this->fileData, true);
    }

    /**
     * Get the file type for the specific file.
     * @return int
     */
    function getFileType()
    {
        return Type::TYPE_JSON;
    }

    /**
     * @return mixed
     */
    public function getJson()
    {
        return $this->json;
    }
}
