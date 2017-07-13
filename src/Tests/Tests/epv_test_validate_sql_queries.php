<?php
/**
 *
 * EPV :: The phpBB Forum Extension Pre Validator.
 *
 * @copyright (c) 2017 phpBB Limited <https://www.phpbb.com>
 * @license       GNU General Public License, version 2 (GPL-2.0)
 *
 */
namespace Phpbb\Epv\Tests\Tests;


use Phpbb\Epv\Files\FileInterface;
use Phpbb\Epv\Files\Type\PHPFileInterface;
use Phpbb\Epv\Output\Output;
use Phpbb\Epv\Output\OutputInterface;
use Phpbb\Epv\Tests\BaseTest;
use Phpbb\Epv\Tests\Exception\TestException;
use Phpbb\Epv\Tests\Type;


class epv_test_validate_sql_queries extends BaseTest
{
	/**
	 * Allowed keywords
	 * 
	 * If line contains one of these keywords, ignore it even when it
	 * has been matched with regular expression.
	 *
	 * @var array
	 */
	protected $allowed_keywords = array(
		'sql_in_set',
		'sql_escape',
		'sql_bit_and',
		'get_visibility_sql',
		'get_sql_where',
		'get_forums_visibility_sql',
		'ORDER BY',
		'ORDER_BY',
	);

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
		$code = $file->getFile();
		$code_exploded = explode("\n", $code);

		if (preg_match_all('/WHERE[^;\$]+[=<>]+[^;]+("|\') \. \$/mU', $code, $matches, PREG_OFFSET_CAPTURE))
		{
			foreach ($matches[0] as $match)
			{
				$prelines = substr_count($code, "\n", 0, $match[1]);
				$inlines = substr_count($match[0], "\n");
				$line = $prelines + $inlines;

				$test = array_reduce($this->allowed_keywords, function($acc, $keyword) use ($code_exploded, $line) {
					return $acc || strpos($code_exploded[$line], $keyword) !== false;
				}, false);
				if ($test)
				{
					continue;
				}

				$this->output->addMessage(Output::WARNING, sprintf('Found potential SQL injection on line %s in %s', $line + 1, $file->getSaveFilename()));
			}
		}
	}

	/**
	 *
	 * @return String
	 */
	public function testName()
	{
		return 'Validate SQL queries';
	}
}
