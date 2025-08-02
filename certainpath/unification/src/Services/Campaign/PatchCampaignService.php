<?php

namespace App\Services\Campaign;

use App\DTO\Request\Campaign\PatchCampaignDTO;
use App\Entity\Campaign;
use App\Entity\CampaignStatus;
use App\Exceptions\NotFoundException\BatchStatusNotFoundException;
use App\Repository\CampaignRepository;
use App\Repository\CampaignStatusRepository;
use App\Services\BatchService;

readonly class PatchCampaignService
{
    public function __construct(
        private CampaignRepository $campaignRepository,
        private CampaignStatusRepository $campaignStatusRepository,
        private BatchService $batchService,
    ) {
    }

    /**
     * @throws BatchStatusNotFoundException
     */
    public function patchCampaign(Campaign $campaign, PatchCampaignDTO $dto): Campaign
    {
        if (in_array('name', $dto->getProvidedFields(), true)) {
            $campaign->setName($dto->name);
        }
        if (in_array('description', $dto->getProvidedFields(), true)) {
            $campaign->setDescription($dto->description);
        }
        if (in_array('phoneNumber', $dto->getProvidedFields(), true)) {
            $campaign->setPhoneNumber($dto->phoneNumber);
        }
        if (in_array('status', $dto->getProvidedFields(), true)) {
            $this->updateCampaignStatus($campaign, $dto);
        }

        $this->campaignRepository->saveCampaign($campaign);

        return $campaign;
    }

    /**
     * @throws BatchStatusNotFoundException
     */
    private function updateCampaignStatus(Campaign $campaign, PatchCampaignDTO $dto): void
    {
        $campaignStatus = $this->campaignStatusRepository->findById($dto->status);
        if (!$campaignStatus) {
            return;
        }

        $campaign->setCampaignStatus($campaignStatus);

        if ($campaignStatus->getName() === CampaignStatus::STATUS_ARCHIVED) {

            foreach ($campaign->getBatches() as $batch) {
                $this->batchService->archiveBatch($batch);
            }
        }
    }
}
