<?php

namespace App\Services\Campaign;

use App\Entity\Batch;
use App\Entity\BatchStatus;
use App\Entity\Campaign;
use App\Entity\CampaignIteration;
use App\Entity\CampaignIterationStatus;
use App\Entity\CampaignStatus;
use App\Exceptions\NotFoundException\BatchStatusNotFoundException;
use App\Exceptions\NotFoundException\CampaignIterationStatusNotFoundException;
use App\Exceptions\NotFoundException\CampaignStatusNotFoundException;
use App\Repository\BatchRepository;
use App\Repository\BatchStatusRepository;
use App\Repository\CampaignIterationRepository;
use App\Repository\CampaignIterationStatusRepository;
use App\Repository\CampaignRepository;
use App\Repository\CampaignStatusRepository;
use App\Services\CampaignEventService;
use DateTime;

readonly class PauseCampaignService
{
    public function __construct(
        private BatchRepository $batchRepository,
        private CampaignRepository $campaignRepository,
        private CampaignEventService $campaignEventService,
        private BatchStatusRepository $batchStatusRepository,
        private CampaignStatusRepository $campaignStatusRepository,
        private CampaignIterationRepository $campaignIterationRepository,
        private CampaignIterationStatusRepository $campaignIterationStatusRepository,
    ) {
    }

    /**
     * @throws BatchStatusNotFoundException
     * @throws CampaignStatusNotFoundException
     * @throws CampaignIterationStatusNotFoundException
     */
    public function pause(Campaign $campaign): void
    {
        $dateToday = new DateTime();

        $campaignStatusPaused = $this->campaignStatusRepository->findOneByNameOrFail(
            CampaignStatus::STATUS_PAUSED
        );
        $batchStatusPaused = $this->batchStatusRepository->findOneByNameOrFail(
            BatchStatus::STATUS_PAUSED
        );
        $campaignIterationStatus = $this->campaignIterationStatusRepository->findOneByNameOrFail(
            CampaignIterationStatus::STATUS_PAUSED
        );
        $uncompletedIterations = $this->campaignIterationRepository->findAllByCampaignIdAndStatus(
            $campaign->getId(),
            [
                CampaignIterationStatus::STATUS_ACTIVE,
                CampaignIterationStatus::STATUS_PENDING,
            ]
        );

        if ($uncompletedIterations->isEmpty()) {
            return;
        }

        /** @var CampaignIteration $iteration */
        foreach ($uncompletedIterations as $iteration) {
            /** @var Batch $batch */
            foreach ($iteration->getBatches() as $batch) {
                $campaignIterationWeek = $batch->getCampaignIterationWeek();
                if (
                    !$campaignIterationWeek ||
                    $campaignIterationWeek->getEndDate() < $dateToday ||
                    $batch->getBatchStatus()?->getName() !== BatchStatus::STATUS_NEW
                ) {
                    continue;
                }

                $batch->setBatchStatus($batchStatusPaused);
                $this->batchRepository->saveBatch($batch);
            }

            $iteration->setCampaignIterationStatus($campaignIterationStatus);
            $this->campaignIterationRepository->saveCampaignIteration($iteration);
        }

        $campaign->setCampaignStatus($campaignStatusPaused);
        $this->campaignRepository->saveCampaign($campaign);

        $this->campaignEventService->createCampaignPausedEvent($campaign);
    }
}
