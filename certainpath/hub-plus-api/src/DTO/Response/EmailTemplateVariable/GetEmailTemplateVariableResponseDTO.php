<?php

declare(strict_types=1);

namespace App\DTO\Response\EmailTemplateVariable;

use App\Entity\EmailTemplateVariable;

class GetEmailTemplateVariableResponseDTO
{
    public function __construct(
        public int $id,
        public string $name,
        public string $description,
        public ?\DateTimeInterface $createdAt,
        public ?\DateTimeInterface $updatedAt,
    ) {
    }

    public static function fromEntity(EmailTemplateVariable $emailTemplate): self
    {
        return new self(
            id: $emailTemplate->getId(),
            name: $emailTemplate->getName(),
            description: $emailTemplate->getDescription(),
            createdAt: $emailTemplate->getCreatedAt(),
            updatedAt: $emailTemplate->getUpdatedAt(),
        );
    }
}
