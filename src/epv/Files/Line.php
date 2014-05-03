<?php
/**
 *
 * @package EPV
 * @copyright (c) 2014 phpBB Group
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */
namespace epv\Files;


class Line implements LineInterface {
    private $file;
    private $lineNr;
    private $line;

    /**
     * @param FileInterface $file
     * @param $lineNr
     * @param $line
     */
    public function __construct(FileInterface $file, $lineNr, $line)
    {
        $this->file = $file;
        $this->lineNr = $lineNr;
        $this->line = $line;
    }

    /**
     * Get the file for this specific line
     * @return FileInterface
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * Get the line number
     * @return int
     */
    public function getLineNr()
    {
        return $this->lineNr;
    }

    /**
     * Get the actual code for this line.
     * @return string
     */
    public function getLine()
    {
        return $this->getLine();
    }
} 