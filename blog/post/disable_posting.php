<?php

include '/var/www/php/sql_functions.php';


$user_info = GetUserInfo($_POST["user_id"], $_POST["user_pw"]);
$posting_info = GetPostingInfo($_POST['posting_index']);
if ($user_info['state'] or $posting_info['state']) {
    echo json_encode(array('state' => 100));
    return;
} else if ($user_info['user_state'] > 0) {
    echo json_encode(array('state' => 200));
    return;
}

if ($user_info['user_index'] == $posting_info['user_index'] or $user_info['user_level'] < 2) {
    if (DisablePosting($_POST['posting_index']))
        echo json_encode(array('state' => 000));
    else
        echo json_encode(array('state' => 100));
} else
    echo json_encode(array('state' => 200));
