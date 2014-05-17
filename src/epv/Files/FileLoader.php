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
use epv\Files\Type\ComposerFile;
use epv\Files\Type\CssFile;
use epv\Files\Type\HTMLFile;
use epv\Files\Type\ImageFile;
use epv\Files\Type\JavascriptFile;
use epv\Files\Type\JsonFile;
use epv\Files\Type\LangFile;
use epv\Files\Type\PHPFile;
use epv\Files\Type\PlainFile;
use epv\Files\Type\ServiceFile;
use epv\Files\Type\XmlFile;
use epv\Files\Type\YmlFile;
use epv\Files\Type\BinaryFile;
use epv\Output\Output;
use epv\Output\OutputInterface;

class FileLoader
{
    /**
     * @var \epv\Output\OutputInterface
     */
    private $output;
    private $debug;
    private $basedir;

    public function __construct(OutputInterface $output, $debug, $basedir)
    {

        $this->output = $output;
        $this->debug = $debug;
        $this->basedir = $basedir;
    }

    public function loadFile($fileName)
    {
        $file = null;

        $split = explode('.', basename($fileName));
        $size = sizeof($split);

        if ($size == 1)
        {
            // File has no extension. If it is a readme file it is ok.
            // Otherwise add notice.
            if (strtolower($fileName) !== 'readme')
            {
                $this->output->addMessage(Output::NOTICE, sprintf("The file %s has no valid extension.", basename($fileName)));
            }
            $file = new PlainFile($this->debug, $fileName);
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
            $this->output->addMessage(Output::ERROR, sprintf("File (%s) seems to have too many dots. Using the last part as extension.", $fileName));
            $file = self::tryLoadFile($fileName, $split[sizeof($split) - 1]);

        }
        else // Blank filename?
        {
            throw new FileException("Filename was empty");
        }

        if ($file == null)
        {
            throw new FileException("Attempted to load an unknown file: $fileName");
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
     * @return BinaryFile|ComposerFile|CssFile|HTMLFile|JavascriptFile|JsonFile|PHPFile|PlainFile|XmlFile|YmlFile|ImageFile|null
     */
    private function tryLoadFile($fileName, $extension, $returnNull = false)
    {
        $this->output->writelnIfDebug("<info>Attempting to load $fileName with extension $extension</info>");

        switch (strtolower($extension))
        {
            case 'php':
                // First, check if this file is a lang file.
                $file = basename($fileName);
                $dir = str_replace($file, '', $fileName);
                $dir = str_replace($this->basedir, '', $fileName);
                $dir = explode('/', $dir);

                if (trim(strtolower($dir[0])) == 'language')
                {
                    return new LangFile($this->debug, $fileName);
                }

                return new PHPFile($this->debug, $fileName);
            case 'html':
            case 'htm':
                return new HTMLFile($this->debug, $fileName);
            case 'json':
                if (strtolower(basename($fileName)) == 'composer.json')
                {
                    return new ComposerFile($this->debug, $fileName);
                }
                else
                {
                    return new JsonFile($this->debug, $fileName);
                }
            case 'yml':
                if (strtolower(basename($fileName)) == 'services.yml')
                {
                    return new ServiceFile($this->debug, $fileName);
                }
                return new YmlFile($this->debug, $fileName);
            case 'txt':
            case 'md':
            case 'htaccess':
                return new PlainFile($this->debug, $fileName);
            case 'xml':
                return new XmlFile($this->debug, $fileName);
            case 'js':
                return new JavascriptFile($this->debug, $fileName);
            case 'css':
                return new CssFile($this->debug, $fileName);
            case 'gif':
            case 'png':
            case 'jpg':
            case 'jpeg':
                return new ImageFile($this->debug, $fileName);

            case 'swf':
                $this->output->addMessage(Output::NOTICE, sprintf("Found an SWF file (%s), please make sure to include the source files for it, as required by the GPL.", basename($fileName)));
                return new BinaryFile($this->debug, $fileName);
            default:
                if ($returnNull)
                {
                    return null;
                }

                $file = basename($fileName);
                $this->output->addMessage(Output::WARNING, "Can not detect the file type for $file, handling it as a binary file");
                return new BinaryFile($this->debug, $fileName);
        }
    }
}