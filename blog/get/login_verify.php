<?php

include '/var/www/php/sql_functions.php';


if (IsNewVisitor() > 0)
    UpdateVisitorCount();

$user_info = GetUserInfo($_GET["user_id"], $_GET["user_pw"]);

if ($user_info['state'] == 0) {
    if ($user_info['user_state'] == 0) {
        UpdateUserLastActionDatetime($user_info);
        $can_write;
        if ($user_info['user_posting_count'] < $user_info['user_posting_limit'])
            $can_write = 1;
        else
            $can_write = 0;
        echo json_encode(array('state' => 000, 'can_write' => $can_write));
    } else {
        echo json_encode(array('state' => 101, 'etc' => '접근 불가 유저입니다.'));
    }
} else {
    echo json_encode(array('state' => $user_info['state'], 'etc' => 'ID 또는 PW가 일치하지 않습니다.'));
}
