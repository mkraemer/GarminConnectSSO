<?php

include __DIR__.'/../vendor/autoload.php';

$username = '...';
$password = '...';

$client = new GuzzleHttp\Client();

$garminSSO = new MKraemer\GarminConnect\SSO\SSO(
    $client,
    $username,
    $password
);

$cookieJar = $garminSSO();

//use this cookieJar for authenticated guzzle requests
var_dump($cookieJar);
