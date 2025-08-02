<?php

declare(strict_types=1);

namespace App\Service\ResourceTag;

use App\Entity\ResourceTag;
use Doctrine\ORM\EntityManagerInterface;

readonly class DeleteResourceTagService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function deleteTag(ResourceTag $tag): void
    {
        $this->entityManager->remove($tag);
        $this->entityManager->flush();
    }
}
