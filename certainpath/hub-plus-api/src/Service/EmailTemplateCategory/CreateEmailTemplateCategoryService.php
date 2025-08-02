<?php

declare(strict_types=1);

namespace App\Service\EmailTemplateCategory;

use App\DTO\Request\CreateUpdateEmailTemplateCategoryDTO;
use App\DTO\Response\EmailTemplateCategory\CreateUpdateEmailTemplateCategoryResponseDTO;
use App\Entity\EmailTemplateCategory;
use App\Exception\CreateUpdateEmailTemplateCategoryException;
use App\Repository\ColorRepository;
use App\Repository\EmailTemplateCategoryRepository;

readonly class CreateEmailTemplateCategoryService
{
    public function __construct(
        private EmailTemplateCategoryRepository $emailTemplateCategoryRepository,
        private ColorRepository $colorRepository,
    ) {
    }

    public function createCategory(CreateUpdateEmailTemplateCategoryDTO $dto): CreateUpdateEmailTemplateCategoryResponseDTO
    {
        $existing = $this->emailTemplateCategoryRepository->findOneByName($dto->name);
        if ($existing) {
            throw new CreateUpdateEmailTemplateCategoryException(sprintf('An EmailTemplateCategory with the name "%s" already exists.', $dto->name));
        }

        $color = $this->colorRepository->find($dto->colorId);
        if (!$color) {
            throw new CreateUpdateEmailTemplateCategoryException(sprintf('Color with id %d not found.', $dto->colorId));
        }

        $category = new EmailTemplateCategory();
        $category->setName($dto->name);
        $category->setDisplayedName($dto->displayedName);
        $category->setDescription($dto->description);
        $category->setColor($color);

        $this->emailTemplateCategoryRepository->save($category, true);

        return new CreateUpdateEmailTemplateCategoryResponseDTO(
            id: $category->getId(),
            name: $category->getName(),
            displayedName: $category->getDisplayedName(),
            description: $category->getDescription(),
            colorId: $color->getId(),
            colorValue: $color->getValue()
        );
    }
}
