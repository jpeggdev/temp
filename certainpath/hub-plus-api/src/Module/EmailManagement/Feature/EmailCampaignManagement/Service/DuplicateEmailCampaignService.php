<?php

declare(strict_types=1);

namespace App\Module\EmailManagement\Feature\EmailCampaignManagement\Service;

use App\Entity\EmailCampaign;
use App\Entity\EmailCampaignStatus;
use App\Module\EmailManagement\Feature\EmailCampaignManagement\DTO\Response\GetEmailCampaignResponseDTO;
use App\Repository\EmailCampaignRepository;
use App\Repository\EmailCampaignStatusRepository;

readonly class DuplicateEmailCampaignService
{
    public function __construct(
        private EmailCampaignRepository $emailCampaignRepository,
        private EmailCampaignStatusRepository $emailCampaignStatusRepository,
    ) {
    }

    public function duplicateEmailCampaign(int $id): GetEmailCampaignResponseDTO
    {
        $emailCampaignToDuplicate = $this->emailCampaignRepository->findOneByIdOrFail($id);
        $emailCampaignStatusDraft = $this->emailCampaignStatusRepository->findOneByNameOrFail(
            EmailCampaignStatus::STATUS_DRAFT
        );

        $duplicatedEmailCampaign = (new EmailCampaign())
            ->setCampaignName($emailCampaignToDuplicate->getCampaignName().' (Copy)')
            ->setDescription($emailCampaignToDuplicate->getDescription())
            ->setEmailSubject($emailCampaignToDuplicate->getEmailSubject())
            ->setEmailTemplate($emailCampaignToDuplicate->getEmailTemplate())
            ->setEvent($emailCampaignToDuplicate->getEvent())
            ->setEventSession($emailCampaignToDuplicate->getEventSession())
            ->setEmailCampaignStatus($emailCampaignStatusDraft);

        $this->emailCampaignRepository->save($duplicatedEmailCampaign, true);

        return GetEmailCampaignResponseDTO::fromEntity($duplicatedEmailCampaign);
    }
}
