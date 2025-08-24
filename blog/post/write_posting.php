<?php

include '/var/www/php/sql_functions.php';


$verify_ret = VerifyPostingData($_POST['posting_title'], $_POST['posting_thumbnail'], $_POST['posting_summary'], $_POST['posting_content']);
if ($verify_ret < 0) {
    if ($verify_ret == -1)
        echo json_encode(array('state' => 200, 'etc' => '잘못된 접근'));
    else if ($verify_ret == -2)
        echo json_encode(array('state' => 100, 'etc' => '제목이 최대 문자열 길이를 초과했습니다.'));
    else if ($verify_ret == -3)
        echo json_encode(array('state' => 100, 'etc' => '썸네일이 최대 문자열 길이를 초과했습니다.'));
    else if ($verify_ret == -4)
        echo json_encode(array('state' => 100, 'etc' => '요약이 최대 문자열 길이를 초과했습니다.'));
    else if ($verify_ret == -5)
        echo json_encode(array('state' => 100, 'etc' => '내용이 최대 문자열 길이를 초과했습니다.'));
    else if ($verify_ret == -6)
        echo json_encode(array('state' => 200, 'etc' => '입력 불가능한 문자열이 포함되어 있습니다.'));
    return;
}

$user_info = GetUserInfo($_POST["user_id"], $_POST["user_pw"]);
if ($user_info['user_posting_count'] + 1 >= $user_info['user_posting_limit']) {
    echo json_encode(array('state' => 100, 'etc' => '글쓰기 횟수 초과했습니다.'));
    return;
}


$return_posting_index = -1;

/* 새로 생성 */
if ($_POST['posting_index'] < 0) {
    $category_info = GetCategoryInfo($_POST['category_index']);
    if ($category_info['category_write_level'] < $user_info['user_level']) {
        echo json_encode(array('state' => 100, 'etc' => '카테고리 쓰기 권한 없음'));
        return;
    }

    $return_posting_index = AddPosting($user_info, $category_info, $_POST['posting_title'], $_POST['posting_thumbnail'], $_POST['posting_summary'], $_POST['posting_content']);
    if ($return_posting_index < 0) {
        echo json_encode(array('state' => 100, 'etc' => '글 쓰기 실패'));
        return;
    }
}
/* 기존 수정 */ else {
    $posting_info = GetPostingInfo($_POST['posting_index']);
    if ($posting_info['user_index'] != $user_info['user_index']) {
        echo json_encode(array('state' => 100, 'etc' => '글 수정 권한 없음'));
        return;
    }

    $return_posting_index = FixPosting($_POST['posting_index'], $_POST['posting_title'], $_POST['posting_thumbnail'], $_POST['posting_summary'], $_POST['posting_content']);
    if ($return_posting_index < 0) {
        echo json_encode(array('state' => 100, 'etc' => '글 수정 실패'));
        return;
    }
    $return_posting_index = $_POST['posting_index'];
}

AddUserWriteCount($user_info);

echo json_encode(array('state' => 000, 'posting_index' => $return_posting_index));
