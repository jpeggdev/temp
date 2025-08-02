<?php

namespace App\Tests\Module\Stochastic\Feature\CampaignManagement\Service;

use App\DTO\Response\StochasticClientMailDataRowDTO;
use App\Module\Stochastic\Feature\CampaignManagement\Service\CampaignService;
use App\Tests\AbstractKernelTestCase;

class CampaignServiceTest extends AbstractKernelTestCase
{
    public function setUp(): void
    {
        $this->doInitializeProducts = true;
        parent::setUp();
    }

    /**
     * @throws \Exception
     */
    public function testHydrateCompanyNameOnDataRowDTO(): void
    {
        //Given
        //Prepare the Postage Data for a Mailing job
        $postageFileToTest = __DIR__.'/../../../../../Files/usps/report-2025.07.21.xls';
        self::assertFileExists($postageFileToTest);
        $testJobId = '00523034';
        $this->getUploadPostageExpenseService()->handleWithDirectFilePath(
            $postageFileToTest,
        );

        //Prepare the Stochastic Mailing Product to Use
        $product = $this->campaignProductRepository->fetchCampaignProducts()[0];

        //Prepare the Test Company
        $company = $this->getTestCompany();

        //Prepare the Service for Hydration
        /** @var CampaignService $campaignService */
        $campaignService = $this->getService(CampaignService::class);

        //Prepare the DTO to Hydrate
        $rowDTO = new StochasticClientMailDataRowDTO(
            id: (int) $testJobId,
            batchNumber: (int) $testJobId,
            intacctId: $company->getIntacctId(),
            clientName: null,
            productId: $product->getId(), // for product injection
            campaignId: 124,
            campaignName: 'Test Campaign',
            batchStatus: 'new',
            prospectCount: 1000,
            week: 30,
            year: 2025,
            startDate: '2025-07-21',
            endDate: '2025-07-21',
            campaignProduct: null,
            batchPricing: null,
            referenceString: $testJobId, // for postage data injection
        );

        //When -- kickoff the Hydration Process
        $hydrated = $campaignService->hydrateStochasticClientMailDataRowDTO(
            $rowDTO
        );

        //Then -- Verify it was properly Hydrated
        self::assertNotNull($hydrated->campaignProduct);
        self::assertNotNull($hydrated->batchPricing);
        self::assertSame(
            $company->getCompanyName(),
            $hydrated->clientName
        );
    }
}
