<?php
namespace RequestInterface;

define('NODE_API_URL', '***');
define('NODE_ACCESS_KEY','***');

$store_id = "***";

include_once "RequestInterface.php";
$url = NODE_API_URL."/pos/store/holidays/info?store_id=".$store_id;
$port = "7293";

$options = [
    "CURLOPT_URL"       =>  $url,
    "CURLOPT_PORT"      =>  $port,
    ];

$RequestInterface = new RequestInterface($options);
$curl_response = $RequestInterface->exec();

print_r($curl_response);
