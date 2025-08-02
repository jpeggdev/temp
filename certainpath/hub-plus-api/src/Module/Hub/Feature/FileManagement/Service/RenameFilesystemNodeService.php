<?php

declare(strict_types=1);

namespace App\Module\Hub\Feature\FileManagement\Service;

use App\Entity\File;
use App\Entity\Folder;
use App\Module\Hub\Feature\FileManagement\DTO\Request\RenameFilesystemNodeRequestDTO;
use App\Module\Hub\Feature\FileManagement\DTO\Response\RenameFilesystemNodeResponseDTO;
use App\Module\Hub\Feature\FileManagement\Exception\FolderOperationException;
use App\Repository\FilesystemNodeRepository;
use Doctrine\ORM\EntityManagerInterface;

readonly class RenameFilesystemNodeService
{
    public function __construct(
        private EntityManagerInterface $em,
        private FilesystemNodeRepository $filesystemNodeRepository,
    ) {
    }

    public function renameNode(string $uuid, RenameFilesystemNodeRequestDTO $dto): RenameFilesystemNodeResponseDTO
    {
        $node = $this->filesystemNodeRepository->findOneBy(['uuid' => $uuid]);

        if (!$node) {
            throw new FolderOperationException('File or folder not found.');
        }

        $existingNode = $this->filesystemNodeRepository->findOneBy([
            'parent' => $node->getParent(),
            'name' => $dto->name,
        ]);

        if ($existingNode && $existingNode->getUuid() !== $uuid) {
            $nodeType = $node instanceof Folder ? 'folder' : 'file';
            throw new FolderOperationException("A $nodeType with this name already exists in this location.");
        }

        if ($node instanceof File) {
            $node->setName($dto->name);
        } elseif ($node instanceof Folder) {
            $node->setName($dto->name);
        }

        $this->em->flush();

        return RenameFilesystemNodeResponseDTO::fromEntity($node);
    }
}
