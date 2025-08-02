<?php

declare(strict_types=1);

namespace App\Service\ResourceCategory;

use App\DTO\Response\ResourceCategory\GetEditResourceCategoryResponseDTO;
use App\Entity\ResourceCategory;

readonly class GetEditResourceCategoryService
{
    public function getEditResourceCategoryDetails(
        ResourceCategory $resourceCategory,
    ): GetEditResourceCategoryResponseDTO {
        return new GetEditResourceCategoryResponseDTO(
            id: $resourceCategory->getId(),
            name: $resourceCategory->getName()
        );
    }
}
