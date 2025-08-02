<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Entity\FilesystemNode;
use App\Service\GetLoggedInUserDTOService;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;

#[AsEntityListener(event: Events::prePersist, method: 'prePersist', entity: FilesystemNode::class)]
#[AsEntityListener(event: Events::preUpdate, method: 'preUpdate', entity: FilesystemNode::class)]
readonly class FilesystemNodeCreatorListener
{
    public function __construct(
        private GetLoggedInUserDTOService $loggedInUserDTOService,
    ) {
    }

    public function prePersist(FilesystemNode $filesystemNode, PrePersistEventArgs $args): void
    {
        if (null !== $filesystemNode->getCreatedBy()) {
            return;
        }

        $loggedInUserDTO = $this->loggedInUserDTOService->getLoggedInUserDTO();
        if (null === $loggedInUserDTO) {
            return; // No logged-in user, do not set updatedBy
        }
        $filesystemNode->setCreatedBy($loggedInUserDTO->getActiveEmployee());
        $filesystemNode->setUpdatedBy($loggedInUserDTO->getActiveEmployee());
    }

    public function preUpdate(FilesystemNode $filesystemNode, PreUpdateEventArgs $args): void
    {
        $loggedInUserDTO = $this->loggedInUserDTOService->getLoggedInUserDTO();
        if (null === $loggedInUserDTO) {
            return; // No logged-in user, do not set updatedBy
        }
        $filesystemNode->setUpdatedBy($loggedInUserDTO->getActiveEmployee());
    }
}
