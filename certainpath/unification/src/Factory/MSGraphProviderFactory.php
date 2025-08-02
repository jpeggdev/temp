<?php

namespace App\Factory;

use GuzzleHttp\Client as GuzzleClient;
use Microsoft\Graph\Graph;

class MSGraphProviderFactory
{
    public static function createGraphClient(
        string $tenantId,
        string $clientId,
        string $clientSecret
    ): Graph {
        $guzzle = new GuzzleClient();
        $url = 'https://login.microsoftonline.com/' . $tenantId . '/oauth2/v2.0/token';

        $token = json_decode(
            $guzzle->post($url, [
                'form_params' => [
                    'client_id' => $clientId,
                    'client_secret' => $clientSecret,
                    'scope' => 'https://graph.microsoft.com/.default',
                    'grant_type' => 'client_credentials',
                ],
            ])->getBody()->getContents()
        );
        $graph = new Graph();
        $graph->setAccessToken($token->access_token);
        return $graph;
    }

    public static function createMockGraphClient(
        string $tenantId,
        string $clientId,
        string $clientSecret
    ): Graph {
        $graph = new Graph();
        $graph->setAccessToken(true);
        return $graph;
    }
}