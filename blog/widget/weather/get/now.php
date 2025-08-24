<?php

include '/var/www/php/sqlcon.php';

$conn = mysqli_connect( $sqlAddr, $sqlId, $sqlPw, $sqlWeatherDb );
$sql_query = 'select * from now where x='.$_GET['x'].' and y='.$_GET['y'];
$result = mysqli_query($conn, $sql_query);
mysqli_close($conn);

$data = array();
while($row = mysqli_fetch_assoc($result))
    $data[] = $row;

echo json_encode(array('state'=>000, 'data'=>$data));

?>