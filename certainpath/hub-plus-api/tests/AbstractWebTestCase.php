<?php

declare(strict_types=1);

namespace App\Tests;

use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Liip\TestFixturesBundle\Services\DatabaseTools\AbstractDatabaseTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Contracts\HttpClient\HttpClientInterface;

abstract class AbstractWebTestCase extends WebTestCase
{
    protected AbstractDatabaseTool $databaseTool;
    protected KernelBrowser $client;

    public function setUp(): void
    {
        $this->client = static::createClient();
        $this->databaseTool = self::getContainer()->get(DatabaseToolCollection::class)->get();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        unset($this->databaseTool);
    }

    protected function getAccessTokenFromAuth0(): string
    {
        $clientId = $_ENV['AUTH0_CLIENT_ID'];
        $clientSecret = $_ENV['AUTH0_CLIENT_SECRET'];
        $audience = $_ENV['AUTH0_AUDIENCE'];
        $domain = $_ENV['AUTH0_DOMAIN'];

        $client = static::getContainer()->get(HttpClientInterface::class);

        $response = $client->request('POST', $domain.'/oauth/token', [
            'json' => [
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
                'audience' => $audience,
                'grant_type' => 'client_credentials',
            ],
        ]);

        $data = $response->toArray();

        if (isset($data['access_token'])) {
            return $data['access_token'];
        }

        throw new \Exception('Failed to fetch Auth0 access token');
    }
}
