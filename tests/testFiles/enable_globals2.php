<?php

if (!defined('IN_PHPBB')) {
  exit;
}
$a = 'enable';
$b = '_super_globals';
$request->{$a}[$b]();

$enable_super_globals = 'enable_super_globals';

$request->$enable_super_globals();
