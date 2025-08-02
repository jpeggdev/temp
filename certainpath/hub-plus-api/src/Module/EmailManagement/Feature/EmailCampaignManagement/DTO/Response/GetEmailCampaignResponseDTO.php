<?php

declare(strict_types=1);

namespace App\Module\EmailManagement\Feature\EmailCampaignManagement\DTO\Response;

use App\DTO\Response\GetEmailCampaignStatusesResponseDTO;
use App\Entity\EmailCampaign;
use App\Entity\EmailCampaignStatus;
use App\Entity\Event;
use App\Module\EmailManagement\Feature\EmailCampaignManagement\Service\GetEmailCampaignSendOptionService;
use App\Module\EmailManagement\Feature\EmailTemplateManagement\DTO\Response\GetEmailTemplateResponseDTO;
use App\Module\EventRegistration\Feature\EventSessionManagement\DTO\Response\GetEventSessionsResponseDTO;

class GetEmailCampaignResponseDTO
{
    public function __construct(
        public int $id,
        public string $campaignName,
        public int $recipientCount,
        public GetEmailCampaignStatusesResponseDTO $emailCampaignStatus,
        public GetEmailTemplateResponseDTO $emailTemplate,
        public array $event,
        public GetEventSessionsResponseDTO $eventSession,
        public array $sendOption,
        public ?\DateTimeInterface $dateSent,
        public ?\DateTimeInterface $createdAt,
        public ?\DateTimeInterface $updatedAt,
        public ?string $description = null,
        public ?string $emailSubject = null,
    ) {
    }

    public static function fromEntity(EmailCampaign $emailCampaign): self
    {
        $emailCampaignStatus = GetEmailCampaignStatusesResponseDTO::fromEntity(
            $emailCampaign->getEmailCampaignStatus()
        );
        $emailTemplate = GetEmailTemplateResponseDTO::fromEntity(
            $emailCampaign->getEmailTemplate()
        );
        $eventSession = GetEventSessionsResponseDTO::fromEntity(
            $emailCampaign->getEventSession()
        );
        $event = self::prepareEventData(
            $emailCampaign->getEvent()
        );
        $recipientCount = self::prepareRecipientCountData(
            $emailCampaign
        );
        $sendOptions = self::prepareSendOptionsData(
            $emailCampaign
        );

        return new self(
            id: $emailCampaign->getId(),
            campaignName: $emailCampaign->getCampaignName(),
            recipientCount: $recipientCount,
            emailCampaignStatus: $emailCampaignStatus,
            emailTemplate: $emailTemplate,
            event: $event,
            eventSession: $eventSession,
            sendOption: $sendOptions,
            dateSent: $emailCampaign->getDateSent(),
            createdAt: $emailCampaign->getCreatedAt(),
            updatedAt: $emailCampaign->getUpdatedAt(),
            description: $emailCampaign->getDescription(),
            emailSubject: $emailCampaign->getEmailSubject(),
        );
    }

    private static function prepareEventData(Event $event): array
    {
        return [
            'id' => $event->getId(),
            'eventName' => $event->getEventName(),
        ];
    }

    private static function prepareRecipientCountData(EmailCampaign $emailCampaign): int
    {
        return EmailCampaignStatus::STATUS_SENT === $emailCampaign->getEmailCampaignStatus()?->getName()
            ? $emailCampaign->getEmailCampaignEventEnrollments()->count()
            : $emailCampaign->getEventSession()?->getEventEnrollments()->count();
    }

    private static function prepareSendOptionsData(EmailCampaign $emailCampaign): array
    {
        $status = $emailCampaign->getEmailCampaignStatus()?->getName();

        if (!in_array($status, [EmailCampaignStatus::STATUS_DRAFT, EmailCampaignStatus::STATUS_SENT], true)) {
            return [];
        }

        return match ($status) {
            EmailCampaignStatus::STATUS_DRAFT => [
                'id' => 1,
                'label' => GetEmailCampaignSendOptionService::SEND_OPTION_SAVE_AS_DRAFT,
            ],
            EmailCampaignStatus::STATUS_SENT => [
                'id' => 2,
                'label' => GetEmailCampaignSendOptionService::SEND_OPTION_SEND_IMMEDIATELY,
            ],
        };
    }
}
