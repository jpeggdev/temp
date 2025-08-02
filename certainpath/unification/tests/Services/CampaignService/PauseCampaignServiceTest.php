<?php

namespace App\Tests\Services\CampaignService;

use App\DTO\Request\Campaign\CreateCampaignDTO;
use App\Entity\BatchStatus;
use App\Entity\CampaignIterationStatus;
use App\Entity\CampaignStatus;
use App\Exceptions\DomainException\Campaign\CampaignAlreadyCreatedException;
use App\Exceptions\DomainException\Campaign\CampaignAlreadyProcessingException;
use App\Exceptions\DomainException\Campaign\CampaignCreationFailedException;
use App\Exceptions\NotFoundException\BatchStatusNotFoundException;
use App\Exceptions\NotFoundException\CampaignIterationStatusNotFoundException;
use App\Exceptions\NotFoundException\CampaignStatusNotFoundException;
use App\Exceptions\NotFoundException\CompanyNotFoundException;
use App\Services\Campaign\PauseCampaignService;
use App\Tests\FunctionalTestCase;
use Carbon\Carbon;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\Exception\ORMException;

class PauseCampaignServiceTest extends FunctionalTestCase
{
    private CreateCampaignDTO $dto;
    private PauseCampaignService $pauseCampaignService;

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

        $this->pauseCampaignService = $this->getPauseCampaignService();

        $company = $this->initializeCompany();
        $mailPackage = $this->initializeMailPackage();

        $mailingFrequencyWeeks = 3;
        $mailingDropWeeks = [1, 2, 3];
        $startDate = Carbon::now()->subWeeks(2)->format('Y-m-d');
        $endDate = Carbon::now()->addMonths(2)->format('Y-m-d');

        $this->dto = $this->prepareCreateCampaignDTO(
            company: $company,
            mailPackage: $mailPackage,
            mailingFrequencyWeeks: $mailingFrequencyWeeks,
            startDate: $startDate,
            endDate: $endDate,
            mailingDropWeeks: $mailingDropWeeks
        );

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
     * @throws BatchStatusNotFoundException
     * @throws CampaignStatusNotFoundException
     * @throws CampaignIterationStatusNotFoundException
     */
    public function testPatchCampaign(): void
    {
        $dateToday = Carbon::now();
        $campaignRepository = $this->getCampaignRepository();

        $campaign = $campaignRepository->findOneByName($this->dto->name);
        $campaignRepository->refreshCampaign($campaign);

        $this->assertEquals(CampaignStatus::STATUS_ACTIVE, $campaign->getCampaignStatus()->getName());
        $this->assertNotEmpty($campaign->getCampaignIterations());
        $this->assertCount(4, $campaign->getCampaignIterations());
        $this->assertNotEmpty($campaign->getBatches());
        $this->assertCount(3, $campaign->getBatches());

        foreach ($campaign->getCampaignIterations() as $iteration) {
            $campaignIterationExpectedStatus = $iteration->getIterationNumber() === 1
                ? CampaignIterationStatus::STATUS_ACTIVE
                : CampaignIterationStatus::STATUS_PENDING;

            $this->assertEquals(
                $campaignIterationExpectedStatus,
                $iteration->getCampaignIterationStatus()->getName()
            );
        }

        foreach ($campaign->getBatches() as $batch) {
            $this->assertEquals(
                BatchStatus::STATUS_NEW,
                $batch->getBatchStatus()->getName()
            );
        }

        $this->pauseCampaignService->pause($campaign);

        $this->assertEquals(CampaignStatus::STATUS_PAUSED, $campaign->getCampaignStatus()->getName());

        foreach ($campaign->getCampaignIterations() as $iteration) {
            $this->assertEquals(
                CampaignIterationStatus::STATUS_PAUSED,
                $iteration->getCampaignIterationStatus()->getName()
            );
        }

        // Ensure that only a single batch (the 3rd one) is updated,
        // as it is the one being processed this week, which includes today's date.
        foreach ($campaign->getBatches() as $batch) {
            $expectedBatchStatus = $batch->getCampaignIterationWeek()->getEndDate() >= $dateToday
                ? BatchStatus::STATUS_PAUSED
                : BatchStatus::STATUS_NEW;


            $this->assertEquals($expectedBatchStatus, $batch->getBatchStatus()->getName());
        }
    }
}
