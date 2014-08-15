<?php
/**
 *
 * @package EPV
 * @copyright (c) 2014 phpBB Group
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */
namespace Phpbb\Epv\Files\Type;


use Phpbb\Epv\Files\FileInterface;

interface YmlFileInterface extends FileInterface{
    /**
     * Get an array with the data in the yaml file.
     *
     * @return array parsed yaml file
     */
    public function getYaml();
}
