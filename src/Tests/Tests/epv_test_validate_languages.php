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
use Phpbb\Epv\Tests\ArrayKeyVisitor;
use Phpbb\Epv\Tests\BaseTest;
use PhpParser\Error;
use PhpParser\NodeTraverser;
use PhpParser\Parser;
use PhpParser\ParserFactory;

class epv_test_validate_languages extends BaseTest
{
	/**
	 * @var Parser
	 */
	private $parser;

	/**
	 * @var ArrayKeyVisitor
	 */
	private $visitor;

	/**
	 * @var NodeTraverser
	 */
	private $traverser;

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
		$this->visitor = new ArrayKeyVisitor;
		$this->traverser = new NodeTraverser;
		$this->traverser->addVisitor($this->visitor);
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
			$this->output->addMessage(OutputInterface::FATAL, 'English language pack is missing');
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
	 * This method scans through all array literals and collects all their string keys.
	 *
	 * @param string $filename File name to a phpBB language file
	 * @return array
	 * @throws Error
	 */
	protected function load_language_keys($filename)
	{
		$contents = @file_get_contents($filename);
		$nodes = $this->parser->parse($contents);
		$this->traverser->traverse($nodes);
		return $this->visitor->get_array_keys();
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
