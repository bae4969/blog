<?php

include '../php/user.php';
include '../php/writeEdit.php';

function getClassLevel($class_index){
    include "/var/www/phpExe/sqlcon.php";

    if($class_index == '')
        return array("class_index"=>0, "read_level"=>4, "write_level"=>4);

    $conn = mysqli_connect( $sqlAddr, $sqlId, $sqlPw, $sqlBlogDb );
    $sql_query = 'select class_index, read_level, write_level from class_list where class_index='.$class_index;
    $result = mysqli_query($conn, $sql_query);
    mysqli_close($conn);

    if($row = mysqli_fetch_assoc($result))
        return $row;

    return array("class_index"=>0, "read_level"=>4, "write_level"=>4);
}

function insertContent($user_index, $level, $class_index, $read_level, $write_level, $title, $thumbnail, $summary, $content){
    include "/var/www/phpExe/sqlcon.php";

    if($user_index < 1) return -1;
    if($class_index < 1) return -1;
    if($level > $write_level) return -1;
    if(($ret = checkContentInput($title, $thumbnail, $summary, $content)) < 0) return $ret;

    $conn = mysqli_connect( $sqlAddr, $sqlId, $sqlPw, $sqlBlogDb );
    $sql_query
        = "insert into contents(user_index, class_index, read_level, write_level, title, thumbnail, summary, content) value(".
        $user_index.",".$class_index.",".$read_level.",".$write_level.",'".addslashes($title)."','".addslashes($thumbnail)."','".addslashes($summary)."','".addslashes($content)."')";
    if(mysqli_query($conn, $sql_query)){
        $sql_query = 'SELECT LAST_INSERT_ID()';
        $result = mysqli_query($conn, $sql_query);
        mysqli_close($conn);
        if($row = mysqli_fetch_assoc($result)){
            $ret = $row['LAST_INSERT_ID()'];
            updateWriteLimit($user_index);
            return $ret;
        }
    }

    return mysqli_error($conn);
}

$user = checkUser($_POST['id'], $_POST['pw']);
if(!checkUserCanWrite($user))
    echo json_encode(array('state'=>100, 'data'=>'하루 글쓰기 수가 초과 되었습니다'));
else{
    $class = getClassLevel($_POST['class_index']);
    $result = insertContent($user['user_index'], $user['level'], $class['class_index'], $class['read_level'], $class['write_level'], $_POST['title'], $_POST['thumbnail'], $_POST['summary'], $_POST['content']);
    if($result == 0)
        echo json_encode(array('state'=>100, 'data'=>'일시적 오류'));
    else if($result == -1)
        echo json_encode(array('state'=>200, 'data'=>'잘못된 접근'));
    else if($result == -2)
        echo json_encode(array('state'=>100, 'data'=>'제목이 최대 문자열 길이를 초과했습니다.'));
    else if($result == -3)
        echo json_encode(array('state'=>100, 'data'=>'썸네일이 최대 문자열 길이를 초과했습니다.'));
    else if($result == -4)
        echo json_encode(array('state'=>100, 'data'=>'요약이 최대 문자열 길이를 초과했습니다.'));
    else if($result == -5)
        echo json_encode(array('state'=>100, 'data'=>'내용이 최대 문자열 길이를 초과했습니다.'));
    else if($result == -6)
        echo json_encode(array('state'=>200, 'data'=>'입력 불가능한 문자열이 포함되어 있습니다.'));
    else
        echo json_encode(array('state'=>000, 'data'=>$result));
}


?>
