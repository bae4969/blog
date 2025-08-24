<?php

include '/var/www/php/sql_functions.php';

$ret = array();
$ret["weekly_visitors"] = GetWeeklyVisitors();

$ret["state"] = 0;

echo json_encode($ret);
