<?php

declare(strict_types=1);

namespace App\Module\EmailManagement\Feature\EmailCampaignActivityLogsManagement\DTO\Response;

use App\Entity\EmailCampaignActivityLog;

class GetEmailCampaignActivityLogsResponseDTO
{
    public function __construct(
        public int $id,
        public string $messageId,
        public string $email,
        public ?string $subject,
        public bool $isSent,
        public bool $isDelivered,
        public bool $isOpened,
        public bool $isClicked,
        public bool $isBounced,
        public bool $isMarkedAsSpam,
        public \DateTimeImmutable $eventSentAt,
    ) {
    }

    public static function fromEntity(EmailCampaignActivityLog $log): self
    {
        return new self(
            id: $log->getId(),
            messageId: $log->getMessageId(),
            email: $log->getEmail(),
            subject: $log->getSubject(),
            isSent: $log->isSent(),
            isDelivered: $log->isDelivered(),
            isOpened: $log->isOpened(),
            isClicked: $log->isClicked(),
            isBounced: $log->isBounced(),
            isMarkedAsSpam: $log->isMarkedAsSpam(),
            eventSentAt: $log->getEventSentAt(),
        );
    }
}
