<?php

include '/var/www/php/sql_functions.php';


$user_info = GetUserInfo($_GET["user_id"], $_GET["user_pw"]);
if ($user_info['state'] != 0 or $user_info['user_state'] != 0) {
	$user_info['user_index'] = -1;
	$user_info['user_level'] = 4;
}

$category_list = GetCategoryListByReadLevel($user_info['user_level']);
if (count($category_list) == 0) {
	echo json_encode(array('state' => 000, 'data' => array()));
	return;
}

echo json_encode(array('state' => 000, 'data' => $category_list));
