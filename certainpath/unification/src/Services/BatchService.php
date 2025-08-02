<?php

namespace App\Services;

use App\DTO\Request\Batch\PatchBatchDTO;
use App\Entity\Batch;
use App\Entity\BatchStatus;
use App\Entity\Campaign;
use App\Entity\CampaignIteration;
use App\Entity\CampaignIterationWeek;
use App\Exceptions\NotFoundException\BatchNotFoundException;
use App\Exceptions\NotFoundException\BatchStatusNotFoundException;
use App\Exceptions\NotFoundException\MailPackageNotFoundException;
use App\Repository\BatchProspectRepository;
use App\Repository\BatchRepository;
use App\Repository\BatchStatusRepository;
use App\ValueObjects\BatchObject;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;

readonly class BatchService
{
    public function __construct(
        private BatchRepository $batchRepository,
        private BatchProspectRepository $batchProspectRepository,
        private BatchStatusRepository $batchStatusRepository,
        private EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * @throws Exception
     * @throws BatchNotFoundException
     * @throws BatchStatusNotFoundException
     * @throws MailPackageNotFoundException
     */

    public function createBatch(
        Campaign $campaign,
        Collection $prospects,
        CampaignIteration $campaignIteration,
        CampaignIterationWeek $campaignIterationWeek
    ): void {
        $mailPackage = $campaign->getMailPackage();
        $batchStatus = $this->batchStatusRepository->findOneByNameOrFail(BatchStatus::STATUS_NEW);

        if (!$mailPackage) {
            throw new MailPackageNotFoundException();
        }

        $batch = $this->saveBatch(
            $campaign,
            $campaignIteration,
            $campaignIterationWeek,
            $batchStatus
        );

        $this->batchProspectRepository->bulkInsertBatchProspects($batch, $prospects);
    }

    /**
     * @throws BatchStatusNotFoundException
     */
    public function patchBatch(Batch $batch, PatchBatchDTO $dto): Batch
    {
        if (in_array('name', $dto->getProvidedFields(), true)) {
            $batch->setName($dto->name);
        }

        if (in_array('description', $dto->getProvidedFields(), true)) {
            $batch->setDescription($dto->description);
        }

        if (in_array('batchStatusId', $dto->getProvidedFields(), true)) {
            $batchStatus = $this->batchStatusRepository->findOneById($dto->batchStatusId);
            if (!$batchStatus) {
                throw new BatchStatusNotFoundException();
            }

            $batch->setBatchStatus($batchStatus);
        }

        if (in_array('batchStatus', $dto->getProvidedFields(), true)) {
            $batchStatus = $this->batchStatusRepository->findOneByName($dto->batchStatusName);
            if (!$batchStatus) {
                throw new BatchStatusNotFoundException();
            }

            $batch->setBatchStatus($batchStatus);
        }

        $this->batchRepository->saveBatch($batch);

        return $batch;
    }

    /**
     * @throws BatchStatusNotFoundException
     */
    public function archiveBatch(Batch $batch): void
    {
        if (!$batch->isNew()) {
            return;
        }

        $statusArchived = $this->batchStatusRepository->findOneByNameOrFail(BatchStatus::STATUS_ARCHIVED);
        $batch->setBatchStatus($statusArchived);
        $this->batchRepository->saveBatch($batch);
    }

    /**
     * @throws Exception
     * @throws BatchNotFoundException
     * @throws MailPackageNotFoundException
     * @throws BatchStatusNotFoundException
     */
    public function resumeBatch(
        Batch $batch,
        Campaign $campaign,
        Collection $batchProspects,
        CampaignIteration $campaignIteration,
        CampaignIterationWeek $campaignIterationWeek,
    ): void {
        // Remove original batch
        $this->entityManager->remove($batch);
        $this->entityManager->flush();

        // Replace the original batch with a new one
        $this->createBatch(
            $campaign,
            $batchProspects,
            $campaignIteration,
            $campaignIterationWeek
        );
    }

    /**
     * @throws Exception
     * @throws BatchNotFoundException
     */
    private function saveBatch(
        Campaign $campaign,
        CampaignIteration $campaignIteration,
        CampaignIterationWeek $campaignIterationWeek,
        BatchStatus $batchStatus
    ): Batch {
        $batchName = $this->prepareBatchName($campaign, $campaignIteration, $campaignIterationWeek);

        $batchObject = (new BatchObject())->fromArray([
            'name' => $batchName,
            'campaign_id' => $campaign->getId(),
            'campaign_iteration_id' => $campaignIteration->getId(),
            'campaign_iteration_week_id' => $campaignIterationWeek->getId(),
            'batch_status_id' => $batchStatus->getId(),
        ]);

        $lastInsertedId = $this->batchRepository->saveBatchDBAL($batchObject);

        $batch = $this->batchRepository->findById($lastInsertedId);
        if (!$batch) {
            throw new BatchNotFoundException();
        }

        return $batch;
    }

    public function prepareBatchName(
        Campaign $campaign,
        CampaignIteration $campaignIteration,
        CampaignIterationWeek $campaignIterationWeek
    ): string {
        return sprintf(
            'Campaign: %s. Batch: %d. Week: %d',
            $campaign->getName(),
            $campaignIteration->getIterationNumber(),
            $campaignIterationWeek->getWeekNumber()
        );
    }
}
