<?php

declare(strict_types=1);

namespace App\Module\EmailManagement\Feature\EmailTemplateManagement\DTO\Response;

use App\DTO\Response\EmailTemplateCategory\GetEmailTemplateCategoryResponseDTO;
use App\Entity\EmailTemplate;
use App\Entity\EmailTemplateCategory;

class GetEmailTemplateResponseDTO
{
    public function __construct(
        public int $id,
        public string $templateName,
        public string $emailSubject,
        public string $emailContent,
        public bool $isActive,
        public array $emailTemplateCategories,
        public ?\DateTimeInterface $createdAt,
        public ?\DateTimeInterface $updatedAt,
    ) {
    }

    public static function fromEntity(EmailTemplate $emailTemplate): self
    {
        return new self(
            id: $emailTemplate->getId(),
            templateName: $emailTemplate->getName(),
            emailSubject: $emailTemplate->getEmailSubject(),
            emailContent: $emailTemplate->getEmailContent(),
            isActive: $emailTemplate->isActive(),
            emailTemplateCategories: self::prepareEmailTemplateCategoriesResponseData($emailTemplate),
            createdAt: $emailTemplate->getCreatedAt(),
            updatedAt: $emailTemplate->getUpdatedAt(),
        );
    }

    public static function prepareEmailTemplateCategoriesResponseData(EmailTemplate $emailTemplate): array
    {
        $categoriesData = [];
        $emailTemplateCategories = $emailTemplate->getEmailTemplateCategories();

        if ($emailTemplateCategories->isEmpty()) {
            return $categoriesData;
        }

        /** @var EmailTemplateCategory $category */
        foreach ($emailTemplateCategories as $key => $category) {
            $categoriesData[$key] = GetEmailTemplateCategoryResponseDTO::fromEntity($category);
        }

        return $categoriesData;
    }
}
