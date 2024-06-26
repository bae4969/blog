<?php

include '/var/www/phpExe/key.php';
include '/var/www/phpExe/sqlcon.php';

$conn = mysqli_connect( $sqlAddr, $sqlId, $sqlPw, $sqlWeatherDb );
$sql_query = 'select * from xyList';
$result = mysqli_query($conn, $sql_query);
mysqli_close($conn);

$xyList = array();
while($row = mysqli_fetch_array($result))
    $xyList[] = $row;

$dateTime_now = new DateTime(date("Y-m-d H").':00:00');

if(date('i') <= 30) $dateTime_now->modify('-1 hour');

$yyyy = $dateTime_now->format('Y');
$MM = $dateTime_now->format('m');
$dd = $dateTime_now->format('d');
$hh = $dateTime_now->format('H');
$mm = '00';

function get_time() { $t=explode(' ',microtime()); return (float)$t[0]+(float)$t[1]; }
$start = get_time();
$api_error_count = 0;
$sql_error_count = 0;
$count = 0;

for($i = 0; $i < count($xyList); $i++){
    $x = $xyList[$i]['x'];
    $y = $xyList[$i]['y'];

    $ch = curl_init();
    $url = 'http://apis.data.go.kr/1360000/VilageFcstInfoService/getUltraSrtNcst';
    $queryParams = '?' . urlencode('ServiceKey') . '='.$gKey;
    $queryParams .= '&' . urlencode('pageNo') . '=' . urlencode('1');
    $queryParams .= '&' . urlencode('numOfRows') . '=' . urlencode('500');
    $queryParams .= '&' . urlencode('dataType') . '=' . urlencode('JSON');
    $queryParams .= '&' . urlencode('base_date') . '=' . urlencode($yyyy.$MM.$dd);
    $queryParams .= '&' . urlencode('base_time') . '=' . urlencode($hh.$mm);
    $queryParams .= '&' . urlencode('nx') . '=' . urlencode($x);
    $queryParams .= '&' . urlencode('ny') . '=' . urlencode($y);

    curl_setopt($ch, CURLOPT_URL, $url . $queryParams);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_HEADER, FALSE);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
    $response = curl_exec($ch);
    curl_close($ch);

    $result = json_decode($response, true);
    if($result['response']['header']['resultCode'] != 0
        || !is_array($result['response']['body']['items']['item']))
    {
        usleep(100000);
        if($count < 5){
            $api_error_count++;
            $count++;
            $i--;
        }
        continue;
    }
    $count = 0;

    $insert_array = array('x'=>$x, 'y'=>$y, 'T1H'=>null, 'REH'=>null, 'WSD'=>null, 'PTY'=>null, 'RN1'=>null);

    $num_data = count($result['response']['body']['items']['item']);
    for($j = 0; $j < $num_data; $j++){
        switch($result['response']['body']['items']['item'][$j]['category']){
            case 'T1H': $insert_array['T1H'] = $result['response']['body']['items']['item'][$j]['obsrValue']; break;
            case 'REH': $insert_array['REH'] = $result['response']['body']['items']['item'][$j]['obsrValue']; break;
            case 'WSD': $insert_array['WSD'] = $result['response']['body']['items']['item'][$j]['obsrValue']; break;
            case 'PTY': $insert_array['PTY'] = $result['response']['body']['items']['item'][$j]['obsrValue']; break;
            case 'RN1': $insert_array['RN1'] = $result['response']['body']['items']['item'][$j]['obsrValue']; break;
        }
    }

    $temp = array();
    if($insert_array['T1H'] != null) $temp['T1H'] = $insert_array['T1H'];
    if($insert_array['REH'] != null) $temp['REH'] = $insert_array['REH'];
    if($insert_array['WSD'] != null) $temp['WSD'] = $insert_array['WSD'];
    if($insert_array['PTY'] != null) $temp['PTY'] = $insert_array['PTY'];
    if($insert_array['RN1'] != null) $temp['RN1'] = $insert_array['RN1'];

    $update_str = implode(', ', array_map(
        function ($k, $v) {
            if(is_array($v))return $k.'[]='.implode('&'.$k.'[]=', $v);
            else            return $k.'='.$v;
        },
        array_keys($temp),
        $temp
    ));

    $conn = mysqli_connect( $sqlAddr, $sqlId, $sqlPw, $sqlWeatherDb );
    $sql_query = 'update now set '.$update_str.' where x='.$x.' and y='.$y;
    if(!mysqli_query($conn, $sql_query)) $sql_error_count++;
    usleep(100000);
}

$end = get_time();
$time = $end - $start;
print('Execute : update weather now (Exe_time : '.number_format($time,6).', API_errors : '.$api_error_count.', SQL_errors : '.$sql_error_count).')';

?>
