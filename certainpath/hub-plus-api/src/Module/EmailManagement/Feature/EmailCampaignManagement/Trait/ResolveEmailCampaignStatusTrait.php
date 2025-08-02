<?php

namespace App\Module\EmailManagement\Feature\EmailCampaignManagement\Trait;

use App\Entity\EmailCampaignStatus;
use App\Exception\UnsupportedSendOptionException;
use App\Module\EmailManagement\Feature\EmailCampaignManagement\Controller\GetEmailCampaignSendOptionsController;

trait ResolveEmailCampaignStatusTrait
{
    /**
     * @throws UnsupportedSendOptionException
     */
    private function resolveEmailCampaignStatus(string $sendOption): EmailCampaignStatus
    {
        return match ($sendOption) {
            GetEmailCampaignSendOptionsController::SEND_OPTION_SAVE_AS_DRAFT => $this->emailCampaignStatusRepository->findOneByNameOrFail(EmailCampaignStatus::STATUS_DRAFT),

            GetEmailCampaignSendOptionsController::SEND_OPTION_SEND_IMMEDIATELY => $this->emailCampaignStatusRepository->findOneByNameOrFail(EmailCampaignStatus::STATUS_SENT),

            GetEmailCampaignSendOptionsController::SEND_OPTION_SCHEDULE_FOR_LATER => $this->emailCampaignStatusRepository->findOneByNameOrFail(EmailCampaignStatus::STATUS_SCHEDULED),

            default => throw new UnsupportedSendOptionException(),
        };
    }
}
