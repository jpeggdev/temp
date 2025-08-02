<?php

namespace App\Tests\Service;

use App\Tests\AbstractKernelTestCase;
use OpenSpout\Common\Exception\IOException;
use OpenSpout\Reader\Exception\ReaderNotOpenedException;

class StochasticCompanyIngestionServiceTest extends AbstractKernelTestCase
{
    /**
     * @throws IOException
     * @throws ReaderNotOpenedException
     * @throws \DateMalformedStringException
     */
    public function testIngestCompanies(): void
    {
        $service = $this->getStochasticCompanyIngestionService();
        self::assertNotNull($service);
        $service->updateAllCompaniesFromStochasticRoster();
        $companies = $service->getActiveCompanies();
        self::assertCount(119, $companies);
        foreach ($companies as $company) {
            self::assertTrue(
                $company->isMarketingEnabled()
            );
        }
    }
}
