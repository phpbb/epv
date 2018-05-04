<?php
/**
 * Modify the data for post submitting
 *
 * @event rxu.postsmerging.posts_merging_end
 * @var	string	mode				Variable containing posting mode value
 * @var	string	subject				Variable containing post subject value
 * @var	string	username			Variable containing post author name
 * @var	int		topic_type			Variable containing topic type value
 * @var	array	poll				Array with the poll data for the post
 * @var	array	data				Array with the data for the post
 * @var	bool	update_message		Flag indicating if the post will be updated
 * @var	bool	update_search_index	Flag indicating if the search index will be updated
 * @var	string	url					The "Return to topic" URL
 * @since 2.0.0
 */
$vars = array(
	'mode',
	'subject',
	'username',
	'topic_type',
	'poll',
	'data',
	'update_message',
	'update_search_index',
	'url',
);
extract($phpbb_dispatcher->trigger_event('rxu.postsmerging.posts_merging_end', compact($vars)));
