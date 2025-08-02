<?php

declare(strict_types=1);

namespace App\Service\ResourceCategory;

use App\Entity\ResourceCategory;
use Doctrine\ORM\EntityManagerInterface;

readonly class DeleteResourceCategoryService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function deleteCategory(ResourceCategory $category): void
    {
        $this->entityManager->remove($category);
        $this->entityManager->flush();
    }
}
