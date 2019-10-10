<?php

if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

$lang = array_merge($lang, array(
	'A' => 'First language string',
	'B' => 'Second language string',
	'C' => [
		1 => 'Singular',
		2 => 'Plural',
	],
));
