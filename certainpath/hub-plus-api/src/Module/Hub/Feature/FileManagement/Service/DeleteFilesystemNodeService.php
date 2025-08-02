<?php

declare(strict_types=1);

namespace App\Module\Hub\Feature\FileManagement\Service;

use App\Entity\Folder;
use App\Module\Hub\Feature\FileManagement\Exception\FileInUseException;
use App\Module\Hub\Feature\FileManagement\Exception\FolderOperationException;
use App\Module\Hub\Feature\FileManagement\Exception\NonEmptyFolderException;
use App\Repository\FilesystemNodeRepository;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;

readonly class DeleteFilesystemNodeService
{
    public function __construct(
        private EntityManagerInterface $em,
        private FilesystemNodeRepository $filesystemNodeRepository,
    ) {
    }

    public function deleteNode(string $uuid): void
    {
        $node = $this->filesystemNodeRepository->findOneBy(['uuid' => $uuid]);

        if (!$node) {
            throw new FolderOperationException('File or folder not found.');
        }

        if ($node instanceof Folder && !$node->getChildren()->isEmpty()) {
            throw new NonEmptyFolderException();
        }

        try {
            $this->em->remove($node);
            $this->em->flush();
        } catch (\Exception $e) {
            if ($e instanceof ForeignKeyConstraintViolationException) {
                throw new FileInUseException();
            }
            throw $e;
        }
    }
}
