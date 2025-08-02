<?php

namespace App\Module\CraftMigration\Service;

use App\DTO\Request\Resource\CreateUpdateResourceDTO;
use App\DTO\Response\UploadTmpFileResponseDTO;
use App\Entity\File;
use App\Module\CraftMigration\DTO\Elements\FileDTO;
use App\Module\CraftMigration\DTO\Request\Resource\UpdateRelatedResourcesDTO;
use App\Repository\FileRepository;
use App\Module\Hub\Feature\FileManagement\Service\UploadFilesystemNodesService;
//use App\Service\File\UploadTmpFileService;
use App\Service\Resource\CreateResourceService;
use App\Service\Resource\GetResourcesService;
use App\Service\Resource\UpdateResourceService;
use App\ValueObject\FileHash;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

readonly class CraftMigrationResourceService
{
    public function __construct(
        private LoggerInterface $logger,
        private UploadFilesystemNodesService $uploadFileService,
        private FileRepository $fileRepo,
        private CreateResourceService $createResourceService,
        private UpdateResourceService $updateResourceService,
        private GetResourcesService $getResourcesService,
        private CraftMigrationMissingFileService $craftMigrationMissingFileService,
    ) {
    }

    public function createOrUpdateFile(FileDTO $file): ?File
    {
        // First, check if file exists locally
        if (!file_exists($file->localFilename)) {
            $this->logger->debug(sprintf('      File does not exist locally: %s', $file->localFilename));
            if (!$this->craftMigrationMissingFileService->downloadMissingFile($file)) {
                $this->logger->error(sprintf('      Failed to download missing file: %s', $file->localFilename));
                return null;
            }
        }

        // Check if file already exists in database by MD5 hash
        $foundFile = $this->findFile($file);
        if ($foundFile) {
            $this->logger->debug(sprintf(
                '      Found existing file with ID: %d, MD5: %s',
                $foundFile->getId(),
                $foundFile->getMd5Hash()
            ));
            return $foundFile;
        }

        // File doesn't exist in database, upload it
        $this->logger->info(sprintf('      File not found in database. Uploading file: %s', $file->localFilename));
        $uploadedfile = new UploadedFile(
            $file->localFilename,
            $file->baseFilename,
            null,
            null,
            true
        );

        $uploadResponse = $this->uploadFileService->uploadFiles([$uploadedfile]);
        if (empty($uploadResponse->files)) {
            $this->logger->error(sprintf('      Failed to upload file: %s', $file->localFilename));
            return null;
        }

        // Get the uploaded file data
        $uploadedFileData = $uploadResponse->files[0];

        // Retrieve the File entity using its UUID
        return $this->fileRepo->findOneByUuid($uploadedFileData['uuid']);
    }

    public function findFile(FileDTO $file): ?File
    {
        $fileHash = FileHash::fromFileSystem($file->localFilename);
        $md5Hash = $fileHash->getString();

        return $this->fileRepo->findOneBy(
            [
                'md5Hash' => $md5Hash,
                'originalFilename' => $file->baseFilename
            ]
        );
    }

    public function getFileId(string $uuid): ?int
    {
        $file = $this->fileRepo->findOneByUuid($uuid);
        return $file?->getId();
    }

    /**
     * @throws Exception
     */
    public function createOrUpdateResource(CreateUpdateResourceDTO|UpdateRelatedResourcesDTO $resourceDTO): int
    {
        $resource = $this->getResourcesService->getResourceBySlug($resourceDTO->slug);
        if ($resource) {
            return $this->updateResourceService->updateResource($resource, $resourceDTO)->id;
        } else {
            return $this->createResourceService->createResource($resourceDTO)->id;
        }
    }
}
