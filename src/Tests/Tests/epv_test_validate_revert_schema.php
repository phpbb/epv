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
use Phpbb\Epv\Files\Type\MigrationFile;
use Phpbb\Epv\Output\Output;
use Phpbb\Epv\Output\OutputInterface;
use Phpbb\Epv\Tests\BaseTest;
use Phpbb\Epv\Tests\Exception\TestException;
use Phpbb\Epv\Tests\Type;
use PhpParser\Error;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\ParserFactory;

class epv_test_validate_revert_schema extends BaseTest
{
	/**
     * @var \PhpParser\Parser
     */
	private $parser;

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

		$this->fileTypeFull = Type::TYPE_PHP | Type::TYPE_MIGRATION;
		$this->parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7);
	}

	/**
	 * @param FileInterface $file
	 *
	 * @throws TestException
	 */
	public function validateFile(FileInterface $file)
	{
		if (!$file instanceof MigrationFile)
		{
			throw new TestException('This test expects a migration file, got ' . get_class($file));
		}

		try
		{
			$nodes = $this->parser->parse($file->getFile());

			if (!$this->parseNodes($nodes))
			{
				$this->output->addMessage(Output::FATAL, sprintf('Migration file %s is missing the revert_schema() method.', $file->getSaveFilename()));
			}
		}
		catch (Error $e)
		{
			$this->output->addMessage(Output::FATAL, 'PHP parse error in file ' . $file->getSaveFilename() . '. Message: ' . $e->getMessage());
		}
	}

	/**
	 * @param Node[] $nodes
	 * @return bool
	 */
	protected function parseNodes($nodes)
	{
		foreach ($nodes as $node)
		{
			if ($node instanceof Class_)
			{
				if (!$this->parseClass($node->stmts))
				{
					return false;
				}
			}
		}

		return true;
	}

	/**
	 * @param Node[] $nodes
	 * @return bool
	 */
	protected function parseClass($nodes)
	{
		foreach ($nodes as $node)
		{
			if ($node instanceof ClassMethod && $node->name === 'update_schema' && !$this->hasMethod($nodes, 'revert_schema'))
			{
				return false;
			}
		}

		return true;
	}

	/**
	 * @param Node[] $nodes
	 * @param string $methodName
	 * @return bool
	 */
	protected function hasMethod($nodes, $methodName)
	{
		foreach ($nodes as $node)
		{
			if ($node instanceof ClassMethod && $node->name === $methodName)
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * @return string
	 */
	public function testName()
	{
		return 'Validate presence of revert_schema()';
	}
}
