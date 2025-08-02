<?php

declare(strict_types=1);

namespace App\Service\ResourceCategory;

use App\DTO\Request\ResourceCategory\GetResourceCategoriesRequestDTO;
use App\DTO\Response\ResourceCategory\GetResourceCategoriesResponseDTO;
use App\Repository\ResourceCategoryRepository;

readonly class GetResourceCategoriesService
{
    public function __construct(
        private ResourceCategoryRepository $resourceCategoryRepository,
    ) {
    }

    /**
     * @return array{
     *     categories: GetResourceCategoriesResponseDTO[],
     *     totalCount: int
     * }
     */
    public function getCategories(GetResourceCategoriesRequestDTO $dto): array
    {
        $categories = $this->resourceCategoryRepository->findCategoriesByQuery($dto);
        $totalCount = $this->resourceCategoryRepository->countCategoriesByQuery($dto);

        $catDtos = array_map(
            fn ($cat) => GetResourceCategoriesResponseDTO::fromEntity($cat),
            $categories
        );

        return [
            'categories' => $catDtos,
            'totalCount' => $totalCount,
        ];
    }
}
