<?php

namespace App\Tests\Service;

use App\Tests\AbstractKernelTestCase;
use OpenSpout\Common\Exception\IOException;
use OpenSpout\Reader\Exception\ReaderNotOpenedException;

class StochasticRosterLoaderServiceTest extends AbstractKernelTestCase
{
    /**
     * @throws IOException
     * @throws ReaderNotOpenedException
     */
    public function testStochasticRosterLoader(): void
    {
        $service = $this->getStochasticRosterLoaderService();
        self::assertNotNull($service);
        $roster = $service->getRoster();
        self::assertCount(264, $roster);
    }
}
