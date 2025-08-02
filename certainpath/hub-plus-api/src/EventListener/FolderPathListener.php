<?php

namespace App\EventListener;

use App\Entity\Folder;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Event\PostUpdateEventArgs;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;

#[AsEntityListener(event: Events::prePersist, method: 'prePersist', entity: Folder::class)]
#[AsEntityListener(event: Events::preUpdate, method: 'preUpdate', entity: Folder::class)]
#[AsEntityListener(event: Events::postPersist, method: 'postPersist', entity: Folder::class)]
#[AsEntityListener(event: Events::postUpdate, method: 'postUpdate', entity: Folder::class)]
class FolderPathListener
{
    private array $foldersToProcess = [];
    private array $processedFolderIds = [];

    public function prePersist(Folder $folder, PrePersistEventArgs $args): void
    {
        $this->updateFolderPath($folder);
    }

    public function preUpdate(Folder $folder, PreUpdateEventArgs $args): void
    {
        if ($args->hasChangedField('name') || $args->hasChangedField('parent')) {
            $this->updateFolderPath($folder);

            $this->foldersToProcess[$folder->getUuid()] = $folder;
        }
    }

    public function postPersist(Folder $folder, PostPersistEventArgs $args): void
    {
        $this->processChildrenPaths($folder, $args->getObjectManager());
    }

    public function postUpdate(Folder $folder, PostUpdateEventArgs $args): void
    {
        foreach ($this->foldersToProcess as $uuid => $folderToProcess) {
            $this->processChildrenPaths($folderToProcess, $args->getObjectManager());
            unset($this->foldersToProcess[$uuid]);
        }

        $this->processedFolderIds = [];
    }

    private function updateFolderPath(Folder $folder): void
    {
        if (null === $folder->getParent()) {
            $folder->setPath('/'.$folder->getName());
        } else {
            $folder->setPath($folder->getParent()->getPath().'/'.$folder->getName());
        }
    }

    private function processChildrenPaths(Folder $folder, EntityManagerInterface $em): void
    {
        if (isset($this->processedFolderIds[$folder->getUuid()])) {
            return;
        }

        $this->processedFolderIds[$folder->getUuid()] = true;

        $childFolders = $folder->getFolders();

        if (0 === $childFolders->count()) {
            return;
        }

        $needsFlush = false;
        $unitOfWork = $em->getUnitOfWork();

        foreach ($childFolders as $childFolder) {
            $oldPath = $childFolder->getPath();
            $newPath = $folder->getPath().'/'.$childFolder->getName();

            if ($oldPath !== $newPath) {
                $childFolder->setPath($newPath);
                $em->persist($childFolder);

                $classMetadata = $em->getClassMetadata(get_class($childFolder));
                $unitOfWork->recomputeSingleEntityChangeSet($classMetadata, $childFolder);

                $needsFlush = true;

                $this->processChildrenPaths($childFolder, $em);
            }
        }

        if ($needsFlush) {
            $em->flush();
        }
    }
}
