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
use Phpbb\Epv\Tests\Type;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

class YmlFile extends BaseFile implements YmlFileInterface
{
	/** @var array */
	protected $yamlFile;

	public function __construct($debug, $filename, $rundir)
	{
		parent::__construct($debug, $filename, $rundir);

		try
		{
			$content = Yaml::parse($this->fileData);

			if (!is_array($content))
			{
				throw new ParseException("Empty file");
			}

			// Look for imports
			if (isset($content['imports']) && is_array($content['imports']))
			{
				// Imports are defined relatively, get the directory based on the current file
				$currentPathInfo = pathinfo($filename);
				$dirname = $currentPathInfo['dirname'];

				foreach ($content['imports'] as $import)
				{
					if (isset($import['resource']))
					{
						try
						{
							$importYmlFileName = $dirname . '/' . $import['resource'];
							$importYmlFile = new YmlFile($debug, $importYmlFileName, $rundir);
							$extraContent = $importYmlFile->getYaml();
						}
						catch (FileLoadException $ex)
						{
							// The imported yml file will be loaded individually later.
							// Let's avoid duplicate error messages here and continue with the current yml.
							$extraContent = array();
						}

						// Imports are at the top of the yaml file, so these should be loaded first.
						// The values of the current yaml file will overwrite existing array values of the imports.
						$content = array_replace_recursive($extraContent, $content);
					}
				}
			}
			$this->yamlFile = $content;
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
