<?php

declare(strict_types=1);

namespace App\Module\Hub\Feature\FileManagement\Service;

use App\Constants\S3Buckets;
use App\DTO\LoggedInUserDTO;
use App\Entity\File;
use App\Module\Hub\Feature\FileManagement\DTO\Response\UploadFilesystemNodesResponseDTO;
use App\Module\Hub\Feature\FileManagement\Util\FileTypeClassifier;
use App\Repository\FilesystemNodeRepository;
use App\Repository\FolderRepository;
use App\Service\AmazonS3Service;
use App\ValueObject\FileHash;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

final readonly class UploadFilesystemNodesService
{
    public function __construct(
        private AmazonS3Service $s3Service,
        private EntityManagerInterface $em,
        private FolderRepository $folderRepository,
        private FilesystemNodeRepository $filesystemNodeRepository,
    ) {
    }

    /**
     * Upload multiple files to S3 and create FilesystemNode entities.
     *
     * @param array<\Symfony\Component\HttpFoundation\File\UploadedFile> $uploadedFiles
     */
    public function uploadFiles(
        array $uploadedFiles,
        ?string $folderUuid = null,
        string $homeDirectory = 'CPA1',
    ): UploadFilesystemNodesResponseDTO {
        $baseFolderPath = "file_manager/{$homeDirectory}";

        $targetFolder = null;
        if ($folderUuid) {
            $targetFolder = $this->folderRepository->findOneBy(['uuid' => $folderUuid]);

            if (!$targetFolder) {
                throw new \InvalidArgumentException('Target folder not found');
            }
        }

        $responseFiles = [];

        foreach ($uploadedFiles as $uploadedFile) {
            $extension = $uploadedFile->guessExtension() ?: 'bin';
            $contentType = $uploadedFile->getMimeType() ?: 'application/octet-stream';
            $originalFilename = $uploadedFile->getClientOriginalName();
            $fileSize = (int) $uploadedFile->getSize();
            $fileType = FileTypeClassifier::classifyByMimeType($contentType);

            $uniqueFilename = $this->generateUniqueFilename($originalFilename, $targetFolder);

            $objectKey = sprintf(
                '%s/%s_%s.%s',
                $baseFolderPath,
                uniqid('file_', true),
                md5((string) microtime(true)),
                $extension
            );

            $fileContent = file_get_contents($uploadedFile->getPathname());
            if (false === $fileContent) {
                throw new FileException('Cannot read uploaded file contents.');
            }

            $fileUrl = $this->s3Service->uploadFile(
                S3Buckets::MEMBERSHIP_FILES_BUCKET,
                $fileContent,
                $objectKey,
                $contentType
            );

            $md5Hash = null;
            if (file_exists($uploadedFile->getPathname())) {
                $fileHash = FileHash::fromFileSystem($uploadedFile->getPathname());
                $md5Hash = $fileHash->getString();
            }

            $fileEntity = new File();
            $fileEntity
                ->setName($uniqueFilename)
                ->setOriginalFilename($originalFilename)
                ->setBucketName(S3Buckets::MEMBERSHIP_FILES_BUCKET)
                ->setObjectKey($objectKey)
                ->setContentType($contentType)
                ->setMimeType($contentType)
                ->setFileSize($fileSize)
                ->setFileType($fileType)
                ->setUrl($fileUrl);

            if (null !== $md5Hash) {
                $fileEntity->setMd5Hash($md5Hash);
            }

            if ($targetFolder) {
                $fileEntity->setParent($targetFolder);
            }

            $this->em->persist($fileEntity);

            $this->em->flush();

            $responseFiles[] = [
                'uuid' => $fileEntity->getUuid(),
                'name' => $fileEntity->getName(),
                'fileType' => $fileEntity->getFileType(),
                'type' => 'file',
                'parentUuid' => $targetFolder ? $targetFolder->getUuid() : null,
                'createdAt' => $fileEntity->getCreatedAt()->format(\DateTimeInterface::ATOM),
                'updatedAt' => $fileEntity->getUpdatedAt()->format(\DateTimeInterface::ATOM),
                'tags' => [],
                'mimeType' => $fileEntity->getMimeType(),
                'fileSize' => $fileEntity->getFileSize(),
                'url' => $fileEntity->getUrl(),
            ];
        }

        return new UploadFilesystemNodesResponseDTO($responseFiles);
    }

    /**
     * Generate a unique filename if a file with the same name already exists
     * Uses the pattern: filename.ext, filename (1).ext, filename (2).ext, etc.
     */
    private function generateUniqueFilename(string $filename, ?object $parentFolder): string
    {
        if (!$this->doesFileNameExist($filename, $parentFolder)) {
            return $filename;
        }

        $extension = pathinfo($filename, PATHINFO_EXTENSION);
        $baseName = $extension ? substr($filename, 0, -(strlen($extension) + 1)) : $filename;

        $counter = 1;
        $newFilename = '';

        do {
            if ($extension) {
                $newFilename = sprintf('%s (%d).%s', $baseName, $counter, $extension);
            } else {
                $newFilename = sprintf('%s (%d)', $baseName, $counter);
            }
            $counter++;
        } while ($this->doesFileNameExist($newFilename, $parentFolder) && $counter < 1000);

        return $newFilename;
    }

    /**
     * Check if a file with the same name exists in the given folder.
     */
    private function doesFileNameExist(string $fileName, ?object $parentFolder): bool
    {
        $criteria = ['name' => $fileName];

        if ($parentFolder) {
            $criteria['parent'] = $parentFolder;
        } else {
            $criteria['parent'] = null;
        }

        return $this->filesystemNodeRepository->count($criteria) > 0;
    }
}
