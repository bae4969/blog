<?php

include '/var/www/php/sqlcon.php';

$conn = mysqli_connect($sqlAddr, $sqlId, $sqlPw, $sqlNaverDb);
$sql_query = 'select * from bitcoin_news';
$result = mysqli_query($conn, $sql_query);
mysqli_close($conn);

$data = array();
while($row = mysqli_fetch_assoc($result))
    $data[] = $row;

echo json_encode(array('state'=>000, 'data'=>$data));

?>
