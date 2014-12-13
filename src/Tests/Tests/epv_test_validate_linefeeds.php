<?php
/**
 *
 * EPV :: The phpBB Forum Extension Pre Validator.
 *
 * @copyright (c) 2014 phpBB Limited <https://www.phpbb.com>
 * @license       GNU General Public License, version 2 (GPL-2.0)
 *
 */
namespace Phpbb\Epv\Tests\Tests;


use Phpbb\Epv\Files\FileInterface;
use Phpbb\Epv\Files\Type\LangFileInterface;
use Phpbb\Epv\Files\Type\PHPFileInterface;
use Phpbb\Epv\Output\Output;
use Phpbb\Epv\Output\OutputInterface;
use Phpbb\Epv\Tests\BaseTest;
use Phpbb\Epv\Tests\Exception\TestException;
use Phpbb\Epv\Tests\Type;
use PHPParser_Error;
use PHPParser_Lexer_Emulative;
use PHPParser_Node;
use PHPParser_Node_Expr_Exit;
use PHPParser_Node_Expr_FuncCall;
use PHPParser_Node_Expr_Print;
use PHPParser_Node_Expr_Variable;
use PHPParser_Node_Stmt_Class;
use PHPParser_Node_Stmt_Echo;
use PHPParser_Node_Stmt_If;
use PHPParser_Node_Stmt_Interface;
use PHPParser_Node_Stmt_Namespace;
use PHPParser_Node_Stmt_Use;
use PHPParser_Parser;


class epv_test_validate_linefeeds extends BaseTest
{
	/**
	 * @param bool            $debug if debug is enabled
	 * @param OutputInterface $output
	 * @param string          $basedir
	 * @param string          $namespace
	 * @param boolean         $titania
	 * @param string          $opendir
	 */
	public function __construct($debug, OutputInterface $output, $basedir, $namespace, $titania, $opendir)
	{
		parent::__construct($debug, $output, $basedir, $namespace, $titania, $opendir);

		$this->fileTypeFull   = Type::TYPE_PHP;
		$this->parser         = new PHPParser_Parser(new PHPParser_Lexer_Emulative());
		$this->totalFileTests = 2;
	}

	/**
	 * @param FileInterface $file
	 *
	 * @throws \Phpbb\Epv\Tests\Exception\TestException
	 */
	public function validateFile(FileInterface $file)
	{
		if (!$file instanceof PHPFileInterface)
		{
			throw new TestException('This test expects a php type, but found something else.');
		}
		$this->file = $file;

		$eols = array_count_values(str_split(preg_replace("/[^\r\n]/", "", $file->getFile())));
		$eola = array_keys($eols, max($eols));
		$eol = implode("", $eola);

		if ($eol == "\n") {
			// Everything is good to go
			return;
		}
		if ($eol == "\r\n") {
			$this->addMessage(Output::FATAL, "Detected windows style newlines instead of UNIX newlines");
		}
		if ($eol == "\r") {
			$this->addMessage(Output::FATAL, "Detected carriage return instead of UNIX newlines");
		}
	}


	/**
	 * Add a new Message to Messages.
	 * The filename is automatically added.
	 *
	 * @param $type
	 * @param $message
	 */
	private function addMessage($type, $message)
	{
		$this->output->addMessage($type, sprintf("%s in %s", $message, $this->file->getSaveFilename()));
	}

	/**
	 *
	 * @return String
	 */
	public function testName()
	{
		return 'Validate php structure and deprecated functions';
	}
}
