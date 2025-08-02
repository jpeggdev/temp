<?php

namespace App\Tests\Services\CampaignService;

use App\DTO\Request\Campaign\CreateCampaignDTO;
use App\Entity\BatchStatus;
use App\Entity\Campaign;
use App\Entity\CampaignStatus;
use App\Entity\Company;
use App\Entity\EventStatus;
use App\Exceptions\DomainException\Campaign\CampaignAlreadyCreatedException;
use App\Exceptions\DomainException\Campaign\CampaignAlreadyProcessingException;
use App\Exceptions\DomainException\Campaign\CampaignCreationFailedException;
use App\Exceptions\DomainException\Campaign\CampaignResumeFailedException;
use App\Exceptions\NotFoundException\BatchStatusNotFoundException;
use App\Exceptions\NotFoundException\CampaignEventNotFoundException;
use App\Exceptions\NotFoundException\CampaignIterationStatusNotFoundException;
use App\Exceptions\NotFoundException\CampaignStatusNotFoundException;
use App\Exceptions\NotFoundException\CompanyNotFoundException;
use App\Services\Campaign\PauseCampaignService;
use App\Services\Campaign\ResumeCampaignService;
use App\Tests\FunctionalTestCase;
use Carbon\Carbon;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\Exception\ORMException;

class ResumeCampaignServiceTest extends FunctionalTestCase
{
    private PauseCampaignService $pauseCampaignService;
    private ResumeCampaignService $resumeCampaignService;
    private CreateCampaignDTO $dto;
    private Company $company;

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
        $this->resumeCampaignService = $this->getResumeCampaignService();
        $this->company = $this->initializeCompany();
        $mailPackage = $this->initializeMailPackage();

        $this->dto = $this->prepareCreateCampaignDTO(
            company: $this->company,
            mailPackage: $mailPackage,
            mailingFrequencyWeeks: 3,
            startDate: Carbon::now()->startOfWeek()->subWeeks(2)->format('Y-m-d'),
            endDate: Carbon::now()->endOfWeek()->addMonths(2)->format('Y-m-d'),
            mailingDropWeeks: [1, 3]
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
     * @throws CampaignResumeFailedException
     * @throws CampaignEventNotFoundException
     * @throws CampaignStatusNotFoundException
     * @throws CampaignIterationStatusNotFoundException
     */
    public function testResumeCampaign(): void
    {
        $campaign = $this->getCampaign();
        $campaignRepository = $this->getCampaignRepository();
        $campaignOriginalData = $this->extractOriginalCampaignData($campaign);

        $this->assertCampaignValid($campaign);
        $originalBatchesData = $this->extractOriginalBatchesData($campaign->getBatches());

        $this->pauseCampaignService->pause($campaign);
        $campaignRepository->refreshCampaign($campaign);
        $this->assertCampaignPaused($campaign);

        $this->simulatePauseForOneWeek($campaign);
        $this->initializeProspects($this->company, 10);

        $campaign = $this->resumeCampaignService->resume($campaign);
        $campaignRepository->refreshCampaign($campaign);

        $this->assertCampaignResumed($campaign, $campaignOriginalData, $originalBatchesData);
    }

    /**
     * @throws ORMException
     */
    private function getCampaign(): Campaign
    {
        $campaignRepository = $this->getCampaignRepository();
        $campaign = $campaignRepository->findOneByName($this->dto->name);
        $campaignRepository->refreshCampaign($campaign);

        return $campaign;
    }

    private function assertCampaignValid(Campaign $campaign): void
    {
        $this->assertEquals(
            CampaignStatus::STATUS_ACTIVE,
            $campaign->getCampaignStatus()->getName()
        );

        $this->assertCount(4, $campaign->getCampaignIterations());
        $this->assertNotEmpty($campaign->getBatches());
        $this->assertCount(2, $campaign->getBatches());
    }

    private function assertCampaignPaused(Campaign $campaign): void
    {
        $this->assertEquals(CampaignStatus::STATUS_PAUSED, $campaign->getCampaignStatus()->getName());

        $expectedCampaignEventStatuses = [
            EventStatus::PENDING,
            EventStatus::PROCESSING,
            EventStatus::CREATED,
            EventStatus::ACTIVE,
            EventStatus::PAUSED,
        ];

        $this->assertCount(5, $campaign->getCampaignEvents());
        foreach ($campaign->getCampaignEvents() as $index => $campaignEvent) {
            $this->assertEquals($expectedCampaignEventStatuses[$index], $campaignEvent->getEventStatus()->getName());
        }

        foreach ($campaign->getCampaignIterations() as $iteration) {
            $this->assertEquals(EventStatus::PAUSED, $iteration->getCampaignIterationStatus()->getName());
        }

        foreach ($campaign->getBatches() as $key => $batch) {
            // The first batch should be new, since it was skipped due to
            // $batch->getCampaignIterationWeek->getEndDate() < $campaignPausedEvent->getCreatedAt()
            $expectedStatus = $key === 0
                ? BatchStatus::STATUS_NEW
                : BatchStatus::STATUS_PAUSED;

            $this->assertEquals($expectedStatus, $batch->getBatchStatus()->getName());
        }
    }

    private function simulatePauseForOneWeek(Campaign $campaign): void
    {
        $campaignEventPaused = $campaign->getCampaignEvents()->last();
        $campaignEventPaused->setCreatedAt(Carbon::now()->subWeek()->toDateTimeImmutable());
        $this->getCampaignEventRepository()->save($campaignEventPaused);
    }

    private function assertCampaignResumed(
        Campaign $campaign,
        array $originalCampaignData,
        array $originalBatchesData
    ): void {
        $this->assertEquals(CampaignStatus::STATUS_ACTIVE, $campaign->getCampaignStatus()->getName());

        $expectedCampaignEventStatuses = [
            EventStatus::PENDING,
            EventStatus::PROCESSING,
            EventStatus::CREATED,
            EventStatus::ACTIVE,
            EventStatus::PAUSED,
            EventStatus::RESUMING,
            EventStatus::ACTIVE
        ];

        $this->assertCount(7, $campaign->getCampaignEvents());
        foreach ($campaign->getCampaignEvents() as $index => $campaignEvent) {
            $this->assertEquals($expectedCampaignEventStatuses[$index], $campaignEvent->getEventStatus()->getName());
        }

        foreach ($campaign->getCampaignIterations() as $iteration) {
            $this->assertEquals(EventStatus::ACTIVE, $iteration->getCampaignIterationStatus()->getName());
        }

        foreach ($campaign->getBatches() as $index => $batch) {
            $index === 0
                ? $this->assertBatchEqualsOriginal($batch, $originalBatchesData[$index])
                : $this->assertBatchWasRecalculated($batch, $originalBatchesData[$index]);
        }

        $this->assetCampaignEndDateWasShifted($campaign, $originalCampaignData);
    }

    private function assertBatchEqualsOriginal($batch, $originalBatchData): void
    {
        $this->assertEquals(
            $batch->getCampaignIterationWeek()->getStartDate()->format('Y-m-d'),
            $originalBatchData['startDate'],
        );

        $this->assertEquals(
            $batch->getCampaignIterationWeek()->getEndDate()->format('Y-m-d'),
            $originalBatchData['endDate'],
        );

        $this->assertEquals(
            $batch->getCampaignIterationWeek()->getWeekNumber(),
            $originalBatchData['weekNumber'],
        );

        $this->assertEquals(
            $batch->getProspects()->count(),
            $originalBatchData['prospectsCount'],
        );
    }

    private function assertBatchWasRecalculated($batch, $originalBatchData): void
    {
        $this->assertNotEquals(
            $batch->getCampaignIterationWeek()->getStartDate()->format('Y-m-d'),
            $originalBatchData['startDate'],
        );

        $this->assertNotEquals(
            $batch->getCampaignIterationWeek()->getEndDate()->format('Y-m-d'),
            $originalBatchData['endDate'],
        );

        $this->assertEquals(
            $batch->getCampaignIterationWeek()->getWeekNumber(),
            $originalBatchData['weekNumber'] + 1,
        );

        $this->assertEquals(
            $batch->getProspects()->count(),
            $originalBatchData['prospectsCount'] + 5,
        );
    }

    private function extractOriginalCampaignData(Campaign $campaign): array
    {
        return [
            'startDate' => $campaign->getStartDate()->format('Y-m-d'),
            'endDate' => $campaign->getEndDate()->format('Y-m-d'),
        ];
    }

    private function extractOriginalBatchesData($originalBatches): array
    {
        $batchesData = [];

        foreach ($originalBatches as $batch) {
            $batchesData[] = [
                'startDate' => $batch->getCampaignIterationWeek()->getStartDate()->format('Y-m-d'),
                'endDate' => $batch->getCampaignIterationWeek()->getEndDate()->format('Y-m-d'),
                'weekNumber' => $batch->getCampaignIterationWeek()->getWeekNumber(),
                'prospectsCount' => $batch->getProspects()->count(),
            ];
        }

        return $batchesData;
    }

    private function assetCampaignEndDateWasShifted(Campaign $campaign, array $campaignOriginalData): void
    {
        $actualEndDate = $campaign->getEndDate()->format('Y-m-d');
        $expectedEndDate = Carbon::createFromFormat('Y-m-d', $campaignOriginalData['endDate'])
            ->addWeek()
            ->endOfWeek()
            ->format('Y-m-d');

        $this->assertEquals($campaign->getStartDate()->format('Y-m-d'), $campaignOriginalData['startDate']);
        $this->assertNotEquals($actualEndDate, $campaignOriginalData['endDate']);
        $this->assertEquals($actualEndDate, $expectedEndDate);
    }
}
