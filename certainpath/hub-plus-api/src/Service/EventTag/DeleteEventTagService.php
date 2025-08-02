<?php

declare(strict_types=1);

namespace App\Service\EventTag;

use App\Entity\EventTag;
use Doctrine\ORM\EntityManagerInterface;

readonly class DeleteEventTagService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function deleteTag(EventTag $tag): void
    {
        $this->entityManager->remove($tag);
        $this->entityManager->flush();
    }
}
