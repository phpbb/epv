<?php
/**
 *
 * @package EPV
 * @copyright (c) 2014 phpBB Group
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */
namespace epv\Files;


use epv\Files\Exception\FileException;

abstract class BaseFile implements FileInterface {

    protected  $fileName;
    protected  $fileData;
    protected  $fileArray;
    protected  $debug;

    /**
     * @param $debug Debug Mode
     * @param $fileName filename for this file
     * @throws Exception\FileException
     */
    public function __construct($debug, $fileName)
    {
        if (!file_exists($fileName))
        {
            throw new FileException("File ({$fileName}) couldn't be found");
        }
        $this->debug = $debug;
        $this->fileName = $fileName;
        $this->fileData = @file_get_contents($this->fileName);

        if ($this->fileData === false)
        {
            throw new FileException("Unable to read file {$fileName}.");
        }
        $this->fileArray = explode("\n", $this->fileData);
    }

    /**
     * @return array
     */
    public function getLines()
    {
        return $this->fileArray;
    }

    /**
     * Get the filename for this file.
     * @return filename
     */
    public function getFilename()
    {
        return $this->fileName;
    }

    /**
     * Get the filedata
     * @return string
     */
    public function getFile()
    {
        return $this->fileData;
    }
} 