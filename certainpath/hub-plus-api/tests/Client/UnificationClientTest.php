<?php

namespace App\Tests\Client;

use App\Tests\AbstractKernelTestCase;
use Symfony\Component\HttpClient\Exception\TransportException;

class UnificationClientTest extends AbstractKernelTestCase
{
    public function testUnificationClientIsConfigured(): void
    {
        $client = $this->getUnificationClient();
        self::assertNotNull($client);
        self::assertIsString($client->getBaseUri());
    }

    public function testUnificationClientThrowsException(): void
    {
        $client = $this->getUnificationClient();
        $this->expectException(TransportException::class);
        $client->sendGetRequest(
            'https://invalid/'
        );
    }
}
