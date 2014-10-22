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


interface FileInterface
{
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

	/*
	 * Get the filename without the full path
	 * @return string
	 */
	function getSaveFilename();

	/**
	 * Get the full file for a specific file.
	 * @return string
	 */
	function getFile();
}
