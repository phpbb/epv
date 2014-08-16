<?php
/**
 *
 * EPV :: The phpBB Forum Extension Pre Validator.
 *
 * @copyright (c) 2014 phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */
class php_exporter_test extends \PHPUnit_Framework_TestCase
{

	public static function setUpBeforeClass()
	{
		require('./tests/Mock/Output.php');
	}

	public function extension_data()
	{
		return array(
			array(27, './tests/events/invalid_name.php', 'rxu.PostsMerging.posts_merging_end', array(
				array(
					'type'    => Phpbb\Epv\Output\OutputInterface::ERROR,
					'message' => 'Event names should be all lowercase in  for event rxu.PostsMerging.posts_merging_end',
				))),
			array(27, './tests/events/valid_name.php', 'rxu.postsmerging.posts_merging_end')
		);
	}

	/**
	 * @dataProvider extension_data
	 */
	public function test_event_name($line, $content, $expected_name, $expected_errors = null)
	{
		$output   = new \Phpbb\Epv\Tests\Mock\Output();
		$exporter = new \Phpbb\Epv\Events\php_exporter($output);
		$exporter->set_content(file($content));

		$name = $exporter->get_event_name($line, false);
		$this->assertEquals($expected_name, $name);

		if ($expected_errors == null)
		{
			$expected_errors = array();
		}
		$this->assertEquals(sizeof($expected_errors), sizeof($output->messages));

		for ($i = 0; $i < sizeof($expected_errors); $i++)
		{
			$this->assertEquals($output->messages[$i]['type'], $expected_errors[$i]['type']);
			$this->assertEquals($output->messages[$i]['message'], $expected_errors[$i]['message']);
		}
	}
}
