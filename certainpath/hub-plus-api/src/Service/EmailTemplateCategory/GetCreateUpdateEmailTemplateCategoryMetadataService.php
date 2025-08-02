<?php

declare(strict_types=1);

namespace App\Service\EmailTemplateCategory;

use App\DTO\Response\EmailTemplateCategory\GetCreateUpdateEmailTemplateCategoryMetadataResponseDTO;
use App\Entity\Color;
use App\Repository\ColorRepository;

final readonly class GetCreateUpdateEmailTemplateCategoryMetadataService
{
    public function __construct(
        private ColorRepository $colorRepository,
    ) {
    }

    public function getMetadata(): GetCreateUpdateEmailTemplateCategoryMetadataResponseDTO
    {
        $colors = $this->colorRepository->findAll();
        $mappedColors = array_map(
            fn (Color $color) => [
                'id' => $color->getId(),
                'value' => $color->getValue(),
            ],
            $colors
        );

        return new GetCreateUpdateEmailTemplateCategoryMetadataResponseDTO(
            colors: $mappedColors
        );
    }
}
