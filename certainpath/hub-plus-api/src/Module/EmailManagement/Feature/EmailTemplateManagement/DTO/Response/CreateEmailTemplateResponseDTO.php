<?php

namespace App\Module\EmailManagement\Feature\EmailTemplateManagement\DTO\Response;

use App\Entity\EmailTemplate;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class CreateEmailTemplateResponseDTO
{
    /**
     * @param Collection<int, array{ id: mixed, name: string, description: string }> $emailTemplateCategories
     */
    public function __construct(
        public int $id,
        public string $templateName,
        public string $emailSubject,
        public string $emailContent,
        public bool $isActive,
        public Collection $emailTemplateCategories,
        public ?\DateTimeInterface $createdAt,
        public ?\DateTimeInterface $updatedAt,
    ) {
    }

    public static function fromEntity(EmailTemplate $emailTemplate): CreateEmailTemplateResponseDTO
    {
        $emailTemplateCategories = self::prepareEmailTemplateCategoriesResponseData($emailTemplate);

        return new self(
            id: $emailTemplate->getId(),
            templateName: $emailTemplate->getName(),
            emailSubject: $emailTemplate->getEmailSubject(),
            emailContent: $emailTemplate->getEmailContent(),
            isActive: $emailTemplate->isActive(),
            emailTemplateCategories: $emailTemplateCategories,
            createdAt: $emailTemplate->getCreatedAt(),
            updatedAt: $emailTemplate->getUpdatedAt(),
        );
    }

    /**
     * @return ArrayCollection<int, array{ id: mixed, name: string, description: string }>
     */
    private static function prepareEmailTemplateCategoriesResponseData(EmailTemplate $emailTemplate): ArrayCollection
    {
        $responseData = new ArrayCollection();

        foreach ($emailTemplate->getEmailTemplateCategories() as $key => $emailTemplateCategory) {
            $responseData->add([
                'id' => $emailTemplateCategory->getId(),
                'name' => $emailTemplateCategory->getName(),
                'description' => $emailTemplateCategory->getDescription(),
            ]);
        }

        return $responseData;
    }
}
