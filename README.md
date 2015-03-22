# GarminConnect SSO Client

This GarminConnect SSO Client facilitates the login process for the GarminConnect SSO service.

When invoked, it returns a GuzzleHttp\Cookie\CookieJar which includes all cookies necessary to make authenticated calls against the GarminConnect API.

## Usage

See examples/login.php

```php
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
```
