<?php

namespace MKraemer\GarminConnect\SSO;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Subscriber\Cookie as CookieSubscriber;

/**
 * MKraemer\GarminConnect\SSO\SSO
 */
class SSO
{
    protected $username;

    protected $password;

    protected $httpClient;

    protected $cookieSubscriber;

    public function __construct(HttpClient $httpClient, $username, $password)
    {
        $this->username = $username;

        $this->password = $password;

        $this->httpClient = $httpClient;

        $this->cookieSubscriber = new CookieSubscriber();
    }

    public function __invoke()
    {
        $flowExecutionKey = $this->getFlowExecutionKey();

        $responseURL = $this->getResponseURL($flowExecutionKey);

        $this->login($responseURL);

        return $this->cookieSubscriber->getCookieJar();
    }

    private function getFlowExecutionKey()
    {
        $response = $this->httpClient->get(
            'https://sso.garmin.com/sso/login',
            [
                'query' => [
                    'service' => 'https://connect.garmin.com/post-auth/login',
                    'clientId' => 'GarminConnect',
                    'consumeServiceTicket' => 'false'
                ],
                'cookies' => $this->cookieSubscriber->getCookieJar(),
                'subscribers' => [$this->cookieSubscriber]
            ]
        );

        preg_match("/flowExecutionKey:\s\[(.*)] -->/", (string) $response->getBody(), $m);

        return $m[1];
    }

    private function getResponseURL($flowExecutionKey)
    {
        $data = [
            'lt'                  => $flowExecutionKey,
            'displayNameRequired' => 'false',
            'username'            => $this->username,
            'password'            => $this->password,
            '_eventId'            => 'submit',
            'embed'               => 'true'
        ];

        $response = $this->httpClient->post(
            'https://sso.garmin.com/sso/login',
            [
                'cookies' => $this->cookieSubscriber->getCookieJar(),
                'subscribers' => [$this->cookieSubscriber],
                'query' => [
                    'service' => 'https://connect.garmin.com/post-auth/login',
                    'clientId' => 'GarminConnect',
                    'consumeServiceTicket' => 'false'
                ],
                'body' => $data
            ]
        );

        preg_match("/response_url\s*=\s*'(.*)'/", (string) $response->getBody(), $m);

        if (empty($m)) {
            throw new \Exception('Authentication failed');
        }

        return $m[1];
    }

    private function login($responseURL)
    {
        $response = $this->httpClient->get(
            $responseURL,
            [
                'cookies' => $this->cookieSubscriber->getCookieJar(),
                'subscribers' => [$this->cookieSubscriber],
                'allow_redirects' => ['max' => 10]
            ]
        );

        if ($response->getStatusCode() !== 200) {
            throw new \Exception('Could not retrieve needed cookies');
        }
    }
}
