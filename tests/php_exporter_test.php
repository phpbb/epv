<?php
/**
 *
 * EPV :: The phpBB Forum Extension Pre Validator.
 *
 * @copyright (c) 2014 phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

use Phpbb\Epv\Events\php_exporter;
use Phpbb\Epv\Output\OutputInterface;
use Phpbb\Epv\Tests\Mock\Output;
use PHPUnit\Framework\TestCase;

class php_exporter_test extends TestCase
{

	public static function setUpBeforeClass(): void
	{
		require_once('./tests/Mock/Output.php');
	}

	public function extension_data()
	{
		$expected_vars = ['mode', 'subject', 'username', 'topic_type', 'poll', 'data', 'update_message', 'update_search_index', 'url'];
		sort($expected_vars);

		$expected_errors = [
			[
				'type'    => OutputInterface::ERROR,
				'message' => 'Event names should be all lowercase in  for event %s',
			],
		];

		return [
			[27, './tests/events/invalid_name_long_multi.php', 'rxu.PostsMerging.posts_merging_end', $expected_vars, $expected_errors],
			[17, './tests/events/invalid_name_long_single.php', 'rxu.PostsMerging.posts_merging_end', $expected_vars, $expected_errors],
			[27, './tests/events/invalid_name_short_multi.php', 'rxu.PostsMerging.posts_merging_end', $expected_vars, $expected_errors],
			[17, './tests/events/invalid_name_short_single.php', 'rxu.PostsMerging.posts_merging_end', $expected_vars, $expected_errors],
			[7, './tests/events/invalid_name_simple.php', 'rxu.PostsMerging.posts_merging_end', [], $expected_errors],
			[27, './tests/events/valid_name_long_multi.php', 'rxu.postsmerging.posts_merging_end', $expected_vars],
			[17, './tests/events/valid_name_long_single.php', 'rxu.postsmerging.posts_merging_end', $expected_vars],
			[27, './tests/events/valid_name_short_multi.php', 'rxu.postsmerging.posts_merging_end', $expected_vars],
			[17, './tests/events/valid_name_short_single.php', 'rxu.postsmerging.posts_merging_end', $expected_vars],
			[7, './tests/events/valid_name_simple.php', 'rxu.postsmerging.posts_merging_end', []],
		];
	}

	/**
	 * @dataProvider extension_data
	 */
	public function test_event_name($line, $content, $expected_name, $expected_vars, $expected_errors = [])
	{
		$output   = new Output();
		$exporter = new php_exporter($output, '');
		$exporter->set_content(explode("\n", file_get_contents($content)));

		$name = $exporter->get_event_name($line, false);
		$exporter->set_current_event($name, $line);
		$vars = $expected_vars ? $exporter->get_vars_from_array(false) : [];

		self::assertEquals($expected_name, $name);
		self::assertEquals($expected_vars, $vars);
		self::assertSameSize($expected_errors, $output->messages);

		for ($i = 0, $iMax = count($expected_errors); $i < $iMax; $i++)
		{
			self::assertEquals($output->messages[$i]['type'], $expected_errors[$i]['type']);
			self::assertEquals($output->messages[$i]['message'], sprintf($expected_errors[$i]['message'], $expected_name));
		}
	}
}
