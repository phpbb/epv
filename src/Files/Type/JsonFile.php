<?php
/**
 *
 * EPV :: The phpBB Forum Extension Pre Validator.
 *
 * @copyright (c) 2014 phpBB Limited <https://www.phpbb.com>
 * @license       GNU General Public License, version 2 (GPL-2.0)
 *
 */
namespace Phpbb\Epv\Files\Type;

use Phpbb\Epv\Files\BaseFile;
use Phpbb\Epv\Tests\Type;

class JsonFile extends BaseFile implements JsonFileInterface
{
	/** @var array */
	private $json;

	public function __construct($debug, $filename, $rundir)
	{
		parent::__construct($debug, $filename, $rundir);
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
