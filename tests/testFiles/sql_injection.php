<?php

$sql = 'SELECT *
	FROM phpbb_posts
	WHERE post_id = ' . $post_id;

$sql = "SELECT *
	FROM phpbb_topics
	WHERE topic_title = '" . $this->db->sql_escape($topic_title) . "'";
