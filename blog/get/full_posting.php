<?php

include '/var/www/php/sql_functions.php';


$is_new_visitor = IsNewVisitor();
$user_info = GetUserInfo($_GET["user_id"], $_GET["user_pw"]);
if ($user_info['state'] != 0 or $user_info['user_state'] != 0) {
	$user_info['user_index'] = -1;
	$user_info['user_level'] = 4;
}

$ret = array('state' => $is_new_visitor, 'data' => array(), 'author' => 0);

$full_posting = GetFullPosting($_GET['posting_index'], $user_info, $is_new_visitor);

$ret = array();
if ($full_posting['state'] != 0) {
	$ret = array('state' => $full_posting['state'], 'data' => array(), 'author' => 0);
} else {
	$ret = array('state' => 000, 'data' => $full_posting);
	if (
		$full_posting['user_index'] == $user_info['user_index'] or
		$user_info['user_level'] < 2
	)
		$ret['author'] = 1;
	else
		$ret['author'] = 0;
}

echo json_encode($ret);
