<?php

namespace App\Services;

use App\Entity\Campaign;
use App\Entity\CampaignIterationStatus;
use App\Entity\CampaignStatus;
use App\Exceptions\DomainException\Campaign\CampaignStopFailedException;
use App\Exceptions\NotFoundException\BatchStatusNotFoundException;
use App\Exceptions\NotFoundException\CampaignIterationStatusNotFoundException;
use App\Exceptions\NotFoundException\CampaignStatusNotFoundException;
use App\Repository\CampaignIterationRepository;
use App\Repository\CampaignIterationStatusRepository;
use App\Repository\CampaignRepository;
use App\Repository\CampaignStatusRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Psr\Log\LoggerInterface;

readonly class StopCampaignService
{
    public function __construct(
        private BatchService $batchService,
        private CampaignRepository $campaignRepository,
        private CampaignStatusRepository $campaignStatusRepository,
        private CampaignIterationRepository $campaignIterationRepository,
        private CampaignIterationStatusRepository $campaignIterationStatusRepository,
        private LoggerInterface $logger,
        private EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * @throws CampaignStopFailedException
     * @throws BatchStatusNotFoundException
     * @throws CampaignStatusNotFoundException
     * @throws CampaignIterationStatusNotFoundException
     */
    public function stop(Campaign $campaign): void
    {
        $this->entityManager->beginTransaction();

        try {
            $campaignStatusArchived = $this->campaignStatusRepository->findOneByNameOrFail(
                CampaignStatus::STATUS_ARCHIVED
            );
            $campaignIterationStatusArchived = $this->campaignIterationStatusRepository->findOneByNameOrFail(
                CampaignIterationStatus::STATUS_ARCHIVED
            );

            foreach ($campaign->getCampaignIterations() as $campaignIteration) {
                $campaignIteration->setCampaignIterationStatus($campaignIterationStatusArchived);
                $this->campaignIterationRepository->save($campaignIteration);
            }
            foreach ($campaign->getBatches() as $batch) {
                $this->batchService->archiveBatch($batch);
            }

            $campaign->setCampaignStatus($campaignStatusArchived);
            $this->campaignRepository->save($campaign);

            $this->entityManager->commit();
        } catch (
            BatchStatusNotFoundException |
            CampaignStatusNotFoundException |
            CampaignIterationStatusNotFoundException
            $e
        ) {
            $this->handleError($e);
            throw $e;
        } catch (Exception $e) {
            $this->handleError($e);
            throw new CampaignStopFailedException();
        }
    }

    private function handleError(Exception $e): void
    {
        $this->logger->error($e->getMessage());
        $this->entityManager->rollback();
    }
}
