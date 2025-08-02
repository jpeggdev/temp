<?php

declare(strict_types=1);

namespace App\Service\ResourceCategory;

use App\DTO\Request\ResourceCategory\CreateUpdateResourceCategoryDTO;
use App\DTO\Response\ResourceCategory\CreateUpdateResourceCategoryResponseDTO;
use App\Entity\ResourceCategory;
use App\Exception\CreateUpdateResourceCategoryException;
use App\Repository\ResourceCategoryRepository;

readonly class EditResourceCategoryService
{
    public function __construct(
        private ResourceCategoryRepository $resourceCategoryRepository,
    ) {
    }

    public function editCategory(
        ResourceCategory $category,
        CreateUpdateResourceCategoryDTO $dto,
    ): CreateUpdateResourceCategoryResponseDTO {
        $existing = $this->resourceCategoryRepository->findOneByName($dto->name);
        if ($existing && $existing->getId() !== $category->getId()) {
            throw new CreateUpdateResourceCategoryException(sprintf('A ResourceCategory with the name "%s" already exists.', $dto->name));
        }

        $category->setName($dto->name);
        $this->resourceCategoryRepository->save($category, true);

        return new CreateUpdateResourceCategoryResponseDTO(
            id: $category->getId(),
            name: $category->getName()
        );
    }
}
