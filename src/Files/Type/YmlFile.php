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
use Phpbb\Epv\Files\Exception\FileLoadException;
use Phpbb\Epv\Output\Messages;
use Phpbb\Epv\Tests\Type;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

class YmlFile extends BaseFile implements YmlFileInterface
{
	protected $yamlFile;

	public function __construct($debug, $filename, $rundir)
	{
		parent::__construct($debug, $filename, $rundir);

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
