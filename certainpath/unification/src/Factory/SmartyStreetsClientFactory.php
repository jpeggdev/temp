<?php

namespace App\Factory;

use SmartyStreets\PhpSdk\ClientBuilder;
use SmartyStreets\PhpSdk\StaticCredentials;
use SmartyStreets\PhpSdk\US_Street\Client;

class SmartyStreetsClientFactory
{
    public static function createClient(string $authId, string $authToken): Client
    {
        $credentials = new StaticCredentials($authId, $authToken);
        return (new ClientBuilder($credentials))->buildUsStreetApiClient();
    }
}
