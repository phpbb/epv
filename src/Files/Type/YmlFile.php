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
use Phpbb\Epv\Files\Exception\FileLoadException;
use Phpbb\Epv\Output\Messages;
use Phpbb\Epv\Tests\Type;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

class YmlFile extends BaseFile implements YmlFileInterface
{
    protected $yamlFile;

    public function __construct($debug, $filename)
    {
        parent::__construct($debug, $filename);

        try
        {
            $this->yamlFile = Yaml::parse($this->fileData);
        }
        catch (ParseException $ex)
        {
            throw new FileLoadException("Parsing yaml file ($filename) failed: " . $ex->getMessage());
        }
    }

    /**
     * Get an array with the data in the yaml file.
     *
     * @return array
     */
    public function getYaml()
    {
        return $this->yamlFile;
    }

    /**
     * Get the file type for the specific file.
     * @return int
     */
    function getFileType()
    {
        return Type::TYPE_YML;
    }
}
