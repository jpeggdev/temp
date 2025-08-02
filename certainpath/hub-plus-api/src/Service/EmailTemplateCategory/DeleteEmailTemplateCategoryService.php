<?php

declare(strict_types=1);

namespace App\Service\EmailTemplateCategory;

use App\Entity\EmailTemplateCategory;
use Doctrine\ORM\EntityManagerInterface;

readonly class DeleteEmailTemplateCategoryService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function deleteCategory(EmailTemplateCategory $category): void
    {
        $this->entityManager->remove($category);
        $this->entityManager->flush();
    }
}
