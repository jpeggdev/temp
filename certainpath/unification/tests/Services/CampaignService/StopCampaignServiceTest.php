<?php

namespace App\Tests\Services\CampaignService;

use App\DTO\Request\Campaign\CreateCampaignDTO;
use App\Entity\BatchStatus;
use App\Entity\CampaignStatus;
use App\Exceptions\DomainException\Campaign\CampaignAlreadyCreatedException;
use App\Exceptions\DomainException\Campaign\CampaignAlreadyProcessingException;
use App\Exceptions\DomainException\Campaign\CampaignCreationFailedException;
use App\Exceptions\DomainException\Campaign\CampaignStopFailedException;
use App\Exceptions\NotFoundException\BatchStatusNotFoundException;
use App\Exceptions\NotFoundException\CampaignIterationStatusNotFoundException;
use App\Exceptions\NotFoundException\CampaignStatusNotFoundException;
use App\Exceptions\NotFoundException\CompanyNotFoundException;
use App\Services\StopCampaignService;
use App\Tests\FunctionalTestCase;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\Exception\ORMException;

class StopCampaignServiceTest extends FunctionalTestCase
{
    private StopCampaignService $stopCampaignService;
    private CreateCampaignDTO $dto;

    /**
     * @throws Exception
     * @throws ORMException
     * @throws CompanyNotFoundException
     * @throws CampaignCreationFailedException
     * @throws CampaignStatusNotFoundException
     * @throws CampaignAlreadyCreatedException
     * @throws CampaignAlreadyProcessingException
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->stopCampaignService = $this->getStopCampaignService();

        $mailPackage = $this->initializeMailPackage();
        $company = $this->initializeCompany();
        $this->dto = $this->prepareCreateCampaignDTO($company, $mailPackage);
        $this->initializeCampaignAsync($this->dto);
    }

    public function tearDown(): void
    {
        $this->entityManager->close();
        unset($this->faker);

        parent::tearDown();
    }

    /**
     * @throws ORMException
     * @throws CampaignStopFailedException
     * @throws BatchStatusNotFoundException
     * @throws CampaignStatusNotFoundException
     * @throws CampaignIterationStatusNotFoundException
     */
    public function testPatchCampaign(): void
    {
        $campaignRepository = $this->getCampaignRepository();

        $campaign = $campaignRepository->findOneByName($this->dto->name);
        $campaignRepository->refreshCampaign($campaign);

        $this->assertEquals(CampaignStatus::STATUS_ACTIVE, $campaign->getCampaignStatus()->getName());
        $this->assertNotEmpty($campaign->getBatches());

        foreach ($campaign->getBatches() as $batch) {
            $this->assertEquals(BatchStatus::STATUS_NEW, $batch->getBatchStatus()->getName());
        }

        $this->stopCampaignService->stop($campaign);

        $this->assertEquals(CampaignStatus::STATUS_ARCHIVED, $campaign->getCampaignStatus()->getName());

        foreach ($campaign->getBatches() as $batch) {
            $this->assertEquals(BatchStatus::STATUS_ARCHIVED, $batch->getBatchStatus()->getName());
        }
    }
}
