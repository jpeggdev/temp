<?php

declare(strict_types=1);

namespace App\DTO\Response;

use App\Entity\EmailCampaignStatus;

class GetEmailCampaignStatusesResponseDTO
{
    public function __construct(
        public int $id,
        public string $name,
        public string $displayName,
    ) {
    }

    public static function fromEntity(EmailCampaignStatus $emailCampaignStatus): self
    {
        return new self(
            id: $emailCampaignStatus->getId(),
            name: $emailCampaignStatus->getName(),
            displayName: ucfirst($emailCampaignStatus->getName())
        );
    }
}
