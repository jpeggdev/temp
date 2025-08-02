<?php

declare(strict_types=1);

namespace App\Service\EmailTemplateCategory;

use App\DTO\Request\GetEmailTemplateCategoriesDTO;
use App\DTO\Response\EmailTemplateCategory\GetEmailTemplateCategoryResponseDTO;
use App\Entity\EmailTemplateCategory;
use App\Repository\EmailTemplateCategoryRepository;

readonly class GetEmailTemplateCategoryService
{
    public function __construct(
        private EmailTemplateCategoryRepository $emailTemplateCategoryRepository,
    ) {
    }

    public function getEmailTemplateCategories(GetEmailTemplateCategoriesDTO $dto): array
    {
        $emailTemplateCategories = $this->emailTemplateCategoryRepository->findByQuery($dto);
        $totalCount = $this->emailTemplateCategoryRepository->getTotalCount($dto);

        $emailTemplateCategoriesDTOs = array_map(
            static fn (EmailTemplateCategory $emailTemplateCategory) => GetEmailTemplateCategoryResponseDTO::fromEntity(
                $emailTemplateCategory
            ),
            $emailTemplateCategories
        );

        return [
            'emailTemplateCategories' => $emailTemplateCategoriesDTOs,
            'totalCount' => $totalCount,
        ];
    }
}
