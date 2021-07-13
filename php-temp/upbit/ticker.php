<?php

include '/var/www/phpExe/sqlcon.php';

$sql_error_count = 0;

$curl = curl_init();
curl_setopt_array($curl, [
CURLOPT_URL => "https://api.upbit.com/v1/market/all?isDetails=false",
CURLOPT_RETURNTRANSFER => true,
CURLOPT_ENCODING => "",
CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
CURLOPT_CUSTOMREQUEST => "GET",
CURLOPT_HTTPHEADER => [
    "Accept: application/json"
],]);
$response = curl_exec($curl);
$err = curl_error($curl);
curl_close($curl);
if ($err) {
    print('Execute : update upbit (API_error : load list)');
    return;
}

$market_data = json_decode($response, true);
$market = array_column($market_data, 'market');
$market_str = implode(",", $market);

$conn = mysqli_connect( $sqlAddr, $sqlId, $sqlPw, $sqlUpbitDb );
for($i = 0; $i < count($market); $i++){
    $sql_quary = 'CREATE TABLE IF NOT EXISTS '.
        str_replace('-', '_', $market[$i]).' (
        date DATETIME NOT NULL,
        value DOUBLE NOT NULL,
        PRIMARY KEY (date))';
    if(!mysqli_query($conn, $sql_quary)){
        $sql_error_count++;
    }
}
mysqli_close($conn);

/////////////////////////////////////////////////////////////////////

$curl = curl_init();
curl_setopt_array($curl, [
CURLOPT_URL => "https://api.upbit.com/v1/ticker?markets=".$market_str,
CURLOPT_RETURNTRANSFER => true,
CURLOPT_ENCODING => "",
CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
CURLOPT_CUSTOMREQUEST => "GET",
CURLOPT_HTTPHEADER => [
    "Accept: application/json"
],]);
$response = curl_exec($curl);
$err = curl_error($curl);
curl_close($curl);
if ($err) {
    print('Execute : update upbit (API_error : load ticker)');
    return;
}

$trade_data = json_decode($response, true);
$datetime_str = date("Y-m-d H:i:",strtotime(date("Y-m-d H:i:s")." +1 minutes")).'00';

$conn = mysqli_connect( $sqlAddr, $sqlId, $sqlPw, $sqlUpbitDb );
for($i = 0; $i < count($trade_data); $i++){
    $sql_quary = 'insert into '.
        str_replace('-', '_', $trade_data[$i]['market']).'(date, value) values("'.
        $datetime_str.'", '.
        $trade_data[$i]['trade_price'].')';
    if(!mysqli_query($conn, $sql_quary)){
        $sql_error_count++;
    }
}
mysqli_close($conn);

print('Execute : update upbit (API_errors : NONE, SQL_errors : '.$sql_error_count.')');

?>