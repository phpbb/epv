<?php

/**
 *
 * EPV :: The phpBB Forum Extension Pre Validator.
 *
 * @copyright (c) 2017 phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace Phpbb\Epv\Tests\Tests;

use Phpbb\Epv\Output\OutputInterface;
use Phpbb\Epv\Tests\BaseTest;
use PhpParser\Error;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Scalar\String_;
use PhpParser\Parser;
use PhpParser\ParserFactory;

class epv_test_validate_languages extends BaseTest
{
	/**
     * @var Parser
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

		$this->directory = true;
		$this->parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7);
	}

	/**
	 * @param array $files
	 *
	 * @return void
	 */
	public function validateDirectory(array $files)
	{
		$langs = [];
		$expected_keys = [];
		$expected_files = [];

		foreach ($files as $file)
		{
			if (preg_match('#^' . preg_quote($this->basedir) . 'language/([a-z_]+?)/(.+\.php)$#', $file, $matches) === 1)
			{
				$language = $matches[1]; // language, e.g. "en"
				$relative_filename = $matches[2]; // file name relative to language's base dir, e.g. "info_acp_ext.php"
				$expected_files[$relative_filename] = $relative_filename;

				try
				{
					$keys = $this->load_language_keys($file);
					$langs[$language][$relative_filename] = $keys;

					$lang_keys = isset($expected_keys[$relative_filename]) ? $expected_keys[$relative_filename] : [];
					$expected_keys[$relative_filename] = array_unique(array_merge($lang_keys, $keys));
				}
				catch (Error $e)
				{
					$this->output->addMessage(OutputInterface::FATAL, 'PHP parse error in file ' . str_replace($this->basedir, '', $file) . '. Message: ' . $e->getMessage());
				}
			}
		}

		if (!empty($langs) && !array_key_exists('en', $langs))
		{
			$this->output->addMessage(OutputInterface::FATAL, sprintf("English language pack is missing"));
		}

		foreach ($langs as $lang_name => $file_contents)
		{
			// Check for missing language files
			foreach (array_diff($expected_files, array_keys($file_contents)) as $missing_file)
			{
				$this->output->addMessage(OutputInterface::NOTICE, sprintf("Language %s is missing the language file %s", $lang_name, $missing_file));
			}

			// Check for missing language keys
			foreach ($file_contents as $relative_filename => $present_keys)
			{
				foreach (array_diff($expected_keys[$relative_filename], $present_keys) as $missing_key)
				{
					$this->output->addMessage(OutputInterface::WARNING, sprintf("Language file %s/%s is missing the language key %s", $lang_name, $relative_filename, $missing_key));
				}
			}
		}
	}

	/**
	 * This method scans through all global-scoped calls to array_merge
	 * and extracts all string keys of all array arguments.
	 *
	 * @param string $filename File name to a phpBB language file
	 * @return array
	 * @throws Error
	 */
	protected function load_language_keys($filename)
	{
		$contents = @file_get_contents($filename);

		$keys = [];

		$nodes = $this->parser->parse($contents);

		foreach ($nodes as $node)
		{
			if ($node instanceof Assign && $node->expr instanceof FuncCall)
			{
				/** @var FuncCall $expr */
				$expr = $node->expr;

				if ($expr->name->getFirst() === 'array_merge')
				{
					for ($i = 1; $i < sizeof($expr->args); $i++)
					{
						/** @var Array_ $array */
						$array = $expr->args[$i]->value;

						if ($array instanceof Array_)
						{
							foreach ($array->items as $item)
							{
								/** @var ArrayItem $item */
								if ($item->key instanceof String_)
								{
									$keys[] = $item->key->value;
								}
								else
								{
									$this->output->addMessage(OutputInterface::NOTICE, 'Language key is not a string value in ' . substr($filename, strlen($this->basedir)) . ' on line ' . $item->key->getLine());
								}
							}
						}
						else
						{
							$this->output->addMessage(OutputInterface::ERROR, sprintf('Expected argument %d of array_merge() to be %s, got %s on line %d', $i + 1, Array_::class, get_class($array), $array->getLine()));
						}
					}
				}
			}
		}

		return $keys;
	}

	/**
	 *
	 * @return String
	 */
	public function testName()
	{
		return 'Test languages';
	}
}
