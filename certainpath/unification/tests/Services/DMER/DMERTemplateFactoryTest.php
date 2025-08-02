<?php

namespace App\Tests\Services\DMER;

use App\Entity\Company;
use App\Entity\Trade;
use App\Services\DMER\DMERTemplateFactory;
use Carbon\Carbon;
use PHPUnit\Framework\TestCase;

class DMERTemplateFactoryTest extends TestCase
{
    private DMERTemplateFactory $service;
    private Company $company;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new DMERTemplateFactory();
        $trade = Trade::electrical();

        $this->company = (new Company())
            ->setName('Test Company')
            ->setIdentifier('TEST1')
            ->addTrade($trade);
        $knownDate = Carbon::create(2022, 9, 27, 12);
        Carbon::setTestNow($knownDate);
    }

    public function testGenerateFileName()
    {
        $this->assertEquals('DMER-2022.xlsm', $this->service::generateFileName(
            $this->company->getPrimaryTradeName(),
            2022
        ));
    }

    public function testGetDefaultTemplate()
    {
        $this->assertEquals(
            DMERTemplateFactory::DEFAULT_DMER_TEMPLATE,
            $this->service->getTemplate($this->company)
        );
    }
}
