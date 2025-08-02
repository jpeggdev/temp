<?php

declare(strict_types=1);

namespace App\Module\Hub\Feature\FileManagement\Service;

use App\Entity\Folder;
use App\Module\Hub\Feature\FileManagement\DTO\Request\CreateFolderRequestDTO;
use App\Module\Hub\Feature\FileManagement\DTO\Response\FolderResponseDTO;
use App\Module\Hub\Feature\FileManagement\Exception\FolderOperationException;
use App\Module\Hub\Feature\FileManagement\Util\FileTypeClassifier;
use App\Repository\FolderRepository;
use Doctrine\ORM\EntityManagerInterface;

readonly class CreateFolderService
{
    private const string DEFAULT_FOLDER_NAME = 'Untitled Folder';

    public function __construct(
        private EntityManagerInterface $em,
        private FolderRepository $folderRepository,
    ) {
    }

    public function createFolder(CreateFolderRequestDTO $dto): FolderResponseDTO
    {
        $parentFolder = null;
        if ($dto->parentFolderUuid) {
            $parentFolder = $this->folderRepository->findOneByUuid($dto->parentFolderUuid);
            if (!$parentFolder) {
                throw new FolderOperationException('Parent folder not found.');
            }
        }

        if ($dto->name !== null) {
            $existingFolder = $this->folderRepository->findOneBy([
                'parent' => $parentFolder,
                'name' => $dto->name,
            ]);

            if ($existingFolder) {
                if ($parentFolder) {
                    throw new FolderOperationException(
                        'A folder with this name already exists in the specified location.'
                    );
                } else {
                    throw new FolderOperationException('A folder with this name already exists at the root level.');
                }
            }

            $folderName = $dto->name;
        } else {
            $folderName = $this->generateUniqueFolderName($parentFolder);
        }

        $folder = new Folder();
        $folder->setName($folderName);
        $folder->setFileSize(0);
        $folder->setParent($parentFolder);
        $folder->setFileType(FileTypeClassifier::TYPE_FOLDER);

        $this->em->persist($folder);
        $this->em->flush();

        return FolderResponseDTO::fromEntity($folder);
    }

    /**
     * Generate a unique folder name using the pattern: "Untitled Folder", "Untitled Folder 2", etc.
     */
    private function generateUniqueFolderName(?Folder $parentFolder): string
    {
        $baseName = self::DEFAULT_FOLDER_NAME;
        $counter = 1;
        $folderName = $baseName;

        while ($this->doesFolderNameExist($folderName, $parentFolder)) {
            $counter++;
            $folderName = sprintf('%s %d', $baseName, $counter);
        }

        return $folderName;
    }

    /**
     * Check if a folder with the given name already exists in the parent folder
     */
    private function doesFolderNameExist(string $folderName, ?Folder $parentFolder): bool
    {
        $criteria = ['name' => $folderName];

        if ($parentFolder) {
            $criteria['parent'] = $parentFolder;
        } else {
            $criteria['parent'] = null;
        }

        return $this->folderRepository->count($criteria) > 0;
    }
}
