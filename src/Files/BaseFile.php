<?php
/**
 *
 * EPV :: The phpBB Forum Extension Pre Validator.
 *
 * @copyright (c) 2014 phpBB Limited <https://www.phpbb.com>
 * @license       GNU General Public License, version 2 (GPL-2.0)
 *
 */
namespace Phpbb\Epv\Files;


use Phpbb\Epv\Files\Exception\FileException;

abstract class BaseFile implements FileInterface
{

	protected $fileName;
	protected $fileData;
	protected $fileArray;
	protected $debug;
	protected $basedir;

	/**
	 * @param $debug     boolean Debug Mode
	 * @param $fileName  string filename for this file
	 * @param $basedir   string namespace for the extension.
	 *
	 * @throws FileException
	 */
	public function __construct($debug, $fileName, $basedir)
	{
		if (!file_exists($fileName))
		{
			throw new FileException(sprintf("File (%s) could not be found", $fileName));
		}
		$this->debug    = $debug;
		$this->fileName = $fileName;
		$this->fileData = @file_get_contents($this->fileName);
		$this->basedir  = $basedir;

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

	public function getSaveFilename()
	{
		return str_replace($this->basedir, '', $this->fileName);
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
