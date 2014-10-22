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
use Phpbb\Epv\Files\Exception\FileLoadException;
use Phpbb\Epv\Files\Type\BinaryFile;
use Phpbb\Epv\Files\Type\ComposerFile;
use Phpbb\Epv\Files\Type\CssFile;
use Phpbb\Epv\Files\Type\HTMLFile;
use Phpbb\Epv\Files\Type\ImageFile;
use Phpbb\Epv\Files\Type\JavascriptFile;
use Phpbb\Epv\Files\Type\JsonFile;
use Phpbb\Epv\Files\Type\LangFile;
use Phpbb\Epv\Files\Type\LockFile;
use Phpbb\Epv\Files\Type\PHPFile;
use Phpbb\Epv\Files\Type\PlainFile;
use Phpbb\Epv\Files\Type\RoutingFile;
use Phpbb\Epv\Files\Type\ServiceFile;
use Phpbb\Epv\Files\Type\XmlFile;
use Phpbb\Epv\Files\Type\YmlFile;
use Phpbb\Epv\Output\Output;
use Phpbb\Epv\Output\OutputInterface;

class FileLoader
{
	/**
	 * @var \Phpbb\Epv\Output\OutputInterface
	 */
	private $output;
	private $debug;
	private $basedir;
	private $loadError;
	private $rundir;

	/**
	 * @param OutputInterface $output
	 * @param                 $debug
	 * @param                 $basedir
	 * @param                 $rundir
	 */
	public function __construct(OutputInterface $output, $debug, $basedir, $rundir)
	{

		$this->output  = $output;
		$this->debug   = $debug;
		$this->basedir = $basedir;
		$this->rundir  = $rundir;
	}

	public function loadFile($fileName)
	{
		$file = null;

		$split = explode('.', basename($fileName));
		$size  = sizeof($split);
		$error = false;

		try
		{
			if ($size == 1)
			{
				// File has no extension. If it is a readme file it is ok.
				// Otherwise add notice.
				if (strtolower($fileName) !== 'readme')
				{
					$this->output->addMessage(Output::NOTICE, sprintf("The file %s has no valid extension.", basename($fileName)));
				}
				$file = new PlainFile($this->debug, $fileName, $this->rundir);
			}
			else if ($size == 2)
			{

				$file = self::tryLoadFile($fileName, $split[1]);
			}
			else if ($size == 3)
			{
				// First, we tried the first extension,
				// Like phpunit-test.xml.all
				// If that has no matches, we try the
				// last extension.

				$file = self::tryLoadFile($fileName, $split[1], true);

				if (!$file)
				{
					$file = self::tryLoadFile($fileName, $split[2]);
				}

			}
			else if ($size >= 4) // Files with 3 ore more dots should not happen.
			{
				$error = true;
				$this->output->addMessage(Output::ERROR, sprintf("File (%s) seems to have too many dots. Using the last part as extension.", $fileName));

				$file = self::tryLoadFile($fileName, $split[sizeof($split) - 1]);

			}
			else // Blank filename?
			{
				throw new FileException("Filename was empty");
			}
		}
		catch (FileLoadException $e)
		{
			$this->output->addMessage(Output::FATAL, $e->getMessage());

			return null;
		}

		return $file;
	}

	/**
	 * Attempts to load a file based on extension.
	 *
	 * In case of plaintext files, contents are also checked to see if it isn't a php file.
	 *
	 * @param $fileName
	 * @param $extension
	 * @param $returnNull boolean Return null in cases where file is not reconised.
	 *
	 * @return BinaryFile|ComposerFile|CssFile|HTMLFile|JavascriptFile|JsonFile|PHPFile|PlainFile|XmlFile|YmlFile|ImageFile|null
	 */
	private function tryLoadFile($fileName, $extension, $returnNull = false)
	{
		$this->output->writelnIfDebug("<info>Attempting to load $fileName with extension $extension</info>");
		$this->loadError = false;

		switch (strtolower($extension))
		{
			case 'php':
				// First, check if this file is a lang file.
				$file = basename($fileName);
				$dir  = str_replace($file, '', $fileName);
				$dir  = str_replace($this->basedir, '', $fileName);
				$dir  = explode('/', $dir);

				if (trim(strtolower($dir[0])) == 'language')
				{
					return new LangFile($this->debug, $fileName, $this->rundir);
				}

				return new PHPFile($this->debug, $fileName, $this->rundir);

			case 'html':
			case 'htm':
				return new HTMLFile($this->debug, $fileName, $this->rundir);

			case 'json':
				if (strtolower(basename($fileName)) == 'composer.json')
				{
					return new ComposerFile($this->debug, $fileName, $this->rundir);
				}
				else
				{
					return new JsonFile($this->debug, $fileName, $this->rundir);
				}

			case 'yml':
				if (strtolower(basename($fileName)) == 'services.yml')
				{
					return new ServiceFile($this->debug, $fileName, $this->rundir);
				}
				if (strtolower(basename($fileName)) == 'routing.yml')
				{
					return new RoutingFile($this->debug, $fileName, $this->rundir);
				}

				return new YmlFile($this->debug, $fileName, $this->rundir);

			case 'txt':
			case 'md':
			case 'htaccess':
			case 'gitattributes':
			case 'gitignore':
			case 'map':
			case 'sh': // Decide if we want a special file type for shell files!
				return new PlainFile($this->debug, $fileName, $this->rundir);

			case 'xml':
				return new XmlFile($this->debug, $fileName, $this->rundir);

			case 'js':
				return new JavascriptFile($this->debug, $fileName, $this->rundir);

			case 'css':
				return new CssFile($this->debug, $fileName, $this->rundir);

			case 'gif':
			case 'png':
			case 'jpg':
			case 'jpeg':
				return new ImageFile($this->debug, $fileName, $this->rundir);

			case 'swf':
				$this->output->addMessage(Output::NOTICE, sprintf("Found an SWF file (%s), please make sure to include the source files for it, as required by the GPL.", basename($fileName)));

				return new BinaryFile($this->debug, $fileName, $this->rundir);
			case 'ds_store':
				$this->output->addMessage(Output::ERROR, sprintf("Found an OS X specific file at %s, please make sure to remove it prior to submission.", $fileName));

				return new BinaryFile($this->debug, $fileName, $this->rundir);

			case 'lock':
				return new LockFile($this->debug, $fileName, $this->rundir);

			default:
				if ($returnNull)
				{
					return null;
				}

				$file = basename($fileName);
				$this->output->addMessage(Output::WARNING, "Can't detect the file type for $file, handling it as a binary file.");

				return new BinaryFile($this->debug, $fileName, $this->rundir);
		}
	}
}
