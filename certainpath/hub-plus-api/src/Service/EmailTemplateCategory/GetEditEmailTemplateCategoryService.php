<?php

declare(strict_types=1);

namespace App\Service\EmailTemplateCategory;

use App\DTO\Response\EmailTemplateCategory\GetEditEmailTemplateCategoryResponseDTO;
use App\Entity\Color;
use App\Entity\EmailTemplateCategory;
use App\Repository\ColorRepository;

readonly class GetEditEmailTemplateCategoryService
{
    public function __construct(
        private ColorRepository $colorRepository,
    ) {
    }

    public function getEditEmailTemplateCategoryDetails(
        EmailTemplateCategory $emailTemplateCategory,
    ): GetEditEmailTemplateCategoryResponseDTO {
        $currentColor = $emailTemplateCategory->getColor();
        $currentColorId = $currentColor?->getId();
        $currentColorValue = $currentColor?->getValue();
        $allColors = $this->colorRepository->findAll();

        $availableColors = array_map(
            fn (Color $color) => [
                'id' => $color->getId(),
                'value' => $color->getValue(),
            ],
            $allColors
        );

        return new GetEditEmailTemplateCategoryResponseDTO(
            id: $emailTemplateCategory->getId(),
            name: $emailTemplateCategory->getName(),
            displayedName: $emailTemplateCategory->getDisplayedName(),
            description: $emailTemplateCategory->getDescription(),
            colorId: $currentColorId,
            colorValue: $currentColorValue,
            availableColors: $availableColors
        );
    }
}
