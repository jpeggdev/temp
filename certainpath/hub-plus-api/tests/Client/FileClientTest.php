<?php

namespace App\Tests\Client;

use App\Tests\AbstractKernelTestCase;

class FileClientTest extends AbstractKernelTestCase
{
    public function testFileClient(): void
    {
        $client = $this->getFileClient();
        self::assertNotNull($client);

        $listObjects = $client->list(
            'stochastic-files',
            'roster/'
        );
        self::assertCount(6, $listObjects);
    }
}
