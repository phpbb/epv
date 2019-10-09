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
));

$b = array(
	'B' => 'Second language string',
);

$lang = array_merge($lang, $b);
