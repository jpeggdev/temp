<?php

namespace App\Tests\Services;

use App\Entity\BatchStatus;
use App\Exceptions\DomainException\Batch\BulkUpdateBatchesStatusesCannotBeCompletedException;
use App\Exceptions\DomainException\Campaign\CampaignAlreadyCreatedException;
use App\Exceptions\DomainException\Campaign\CampaignAlreadyProcessingException;
use App\Exceptions\DomainException\Campaign\CampaignCreationFailedException;
use App\Exceptions\NotFoundException\BatchStatusNotFoundException;
use App\Exceptions\NotFoundException\CampaignStatusNotFoundException;
use App\Exceptions\NotFoundException\CompanyNotFoundException;
use App\Repository\BatchRepository;
use App\Repository\BulkBatchStatusEventRepository;
use App\Services\BulkUpdateBatchesStatusService;
use App\Tests\FunctionalTestCase;
use Carbon\Carbon;
use Doctrine\ORM\Exception\ORMException;

class BulkUpdateBatchesStatusServiceTest extends FunctionalTestCase
{
    private BatchRepository $batchRepository;
    private BulkUpdateBatchesStatusService $bulkUpdateBatchesStatusService;
    private BulkBatchStatusEventRepository $batchStatusBulkEventRepository;

    public function setUp(): void
    {
        parent::setUp();

        $this->batchRepository = $this->getBatchRepository();
        $this->bulkUpdateBatchesStatusService = $this->getBulkUpdateBatchesStatusService();
        $this->batchStatusBulkEventRepository = $this->getBatchStatusBulkEventRepository();
    }

    /**
     * @throws ORMException
     * @throws CompanyNotFoundException
     * @throws BatchStatusNotFoundException
     * @throws CampaignAlreadyCreatedException
     * @throws CampaignCreationFailedException
     * @throws CampaignStatusNotFoundException
     * @throws CampaignAlreadyProcessingException
     * @throws BulkUpdateBatchesStatusesCannotBeCompletedException
     */
    public function testBatchesStatusIsUpdatedAndBatchStatusBulkEventIsCreated(): void
    {
        $company = $this->initializeCompany();
        $mailPackage = $this->initializeMailPackage();

        $dto1 = $this->prepareCreateCampaignDTO(
            company: $company,
            mailPackage: $mailPackage,
            mailingFrequencyWeeks: 3,
            startDate: Carbon::now()->subWeeks(2)->format('Y-m-d'),
            endDate: Carbon::now()->addMonths(2)->format('Y-m-d'),
            mailingDropWeeks: [1, 2, 3]
        );

        $dto2 = $this->prepareCreateCampaignDTO(
            company: $company,
            mailPackage: $mailPackage,
            mailingFrequencyWeeks: 4,
            startDate: Carbon::now()->format('Y-m-d'),
            endDate: Carbon::now()->addMonths(4)->format('Y-m-d'),
            mailingDropWeeks: [1, 2, 3, 4]
        );

        $this->initializeCampaignAsync($dto1);
        $this->initializeCampaignAsync($dto2, initDependencies: false);

        $year = Carbon::now()->year;
        $week = Carbon::now()->week;
        $weekStartDate = Carbon::now()->startOfWeek();
        $weekEndDate = Carbon::now()->endOfWeek();

        $batchesToUpdate = $this->batchRepository->findAllByWeekStartAndEndDate($weekStartDate, $weekEndDate);
        $this->assertCount(2, $batchesToUpdate);

        foreach ($batchesToUpdate as $batch) {
            $this->assertEquals(BatchStatus::STATUS_NEW, $batch->getBatchStatus()->getName());
        }

        $batchStatusBulkEvents = $this->batchStatusBulkEventRepository->findAllByYearAndWeek(
            $year,
            $week,
        );
        $this->assertEmpty($batchStatusBulkEvents);

        $updatedBatchesAudit = $this->bulkUpdateBatchesStatusService->bulkUpdateStatus(
            $year,
            $week,
            BatchStatus::STATUS_SENT
        );

        $batches = $this->batchRepository->findAllByWeekStartAndEndDate($weekStartDate, $weekEndDate);
        $this->assertCount(2, $batches);
        foreach ($batches as $batch) {
            $this->assertEquals(BatchStatus::STATUS_SENT, $batch->getBatchStatus()->getName());
        }

        $batchStatusBulkEvents = $this->batchStatusBulkEventRepository->findAllByYearAndWeek(
            $year,
            $week,
        );
        $this->assertCount(1, $batchStatusBulkEvents);
        $batchStatusBulkEventSent = $batchStatusBulkEvents->first();

        $this->assertEquals($year, $batchStatusBulkEventSent->getYear());
        $this->assertEquals($week, $batchStatusBulkEventSent->getWeek());
        $this->assertEquals(BatchStatus::STATUS_SENT, $batchStatusBulkEventSent->getBatchStatus()->getName());
        $this->assertEquals($updatedBatchesAudit, $batchStatusBulkEventSent->getUpdatedBatches());
    }
}
