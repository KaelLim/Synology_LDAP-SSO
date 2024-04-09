<?php
$accesstoken = $_GET['accesstoken'];

function httpGet($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); // For testing, ignore CA checking
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $output = curl_exec($ch);
    curl_close($ch);
    return $output;
}

$url_str = "https://taipei-3in1-nas.synology.me:6322/webman/sso/SSOAccessToken.cgi?action=exchange&access_token=" . $accesstoken;
header('Content-Type: application/json');
echo httpGet($url_str);
?>