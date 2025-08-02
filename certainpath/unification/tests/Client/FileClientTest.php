<?php

namespace App\Tests\Client;

use App\Exceptions\FileCouldNotBeRetrieved;
use App\Exceptions\StochasticFilePathWasInvalid;
use App\Tests\FunctionalTestCase;
use App\ValueObjects\StochasticFile;
use OpenSpout\Common\Exception\IOException;
use OpenSpout\Reader\Exception\ReaderNotOpenedException;

class FileClientTest extends FunctionalTestCase
{
    /**
     * @group remoteResources
     */
    public function testFailedRetrieval(): void
    {
        $client = $this->getFileClient();
        self::assertNotNull($client);
        $this->expectException(
            FileCouldNotBeRetrieved::class
        );
        $client->download(
            'stochastic-files',
            'sync/lists/'
        );
    }
}
