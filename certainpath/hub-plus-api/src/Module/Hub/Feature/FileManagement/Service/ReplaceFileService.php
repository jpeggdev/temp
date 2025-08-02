<?php

declare(strict_types=1);

namespace App\Module\Hub\Feature\FileManagement\Service;

use App\Constants\S3Buckets;
use App\DTO\LoggedInUserDTO;
use App\Module\Hub\Feature\FileManagement\DTO\Response\ReplaceFileResponseDTO;
use App\Module\Hub\Feature\FileManagement\Exception\FileReplacementException;
use App\Module\Hub\Feature\FileManagement\Exception\FilesystemNodeNotFoundException;
use App\Module\Hub\Feature\FileManagement\Util\FileTypeClassifier;
use App\Repository\FileRepository;
use App\Repository\FilesystemNodeRepository;
use App\Service\AmazonS3Service;
use App\ValueObject\FileHash;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

readonly class ReplaceFileService
{
    public function __construct(
        private AmazonS3Service $s3Service,
        private EntityManagerInterface $em,
        private FileRepository $fileRepository,
        private FilesystemNodeRepository $filesystemNodeRepository,
    ) {
    }

    public function replaceFile(
        string $fileUuid,
        UploadedFile $uploadedFile,
        LoggedInUserDTO $loggedInUserDTO,
    ): ReplaceFileResponseDTO {
        $file = $this->fileRepository->findOneByUuid($fileUuid);
        if (!$file) {
            throw new FilesystemNodeNotFoundException('File not found.');
        }

        $extension = $uploadedFile->guessExtension() ?: 'bin';
        $contentType = $uploadedFile->getMimeType() ?: 'application/octet-stream';
        $originalFilename = $uploadedFile->getClientOriginalName();
        $fileSize = (int) $uploadedFile->getSize();
        $fileType = FileTypeClassifier::classifyByMimeType($contentType);
        $company = $loggedInUserDTO->getActiveCompany();
        $companyId = $company->getId();

        $parentFolder = $file->getParent();

        $uniqueFilename = $this->generateUniqueFilename($originalFilename, $parentFolder);

        $baseFolderPath = "file_manager/company_{$companyId}";
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

        $md5Hash = null;
        if (file_exists($uploadedFile->getPathname())) {
            $fileHash = FileHash::fromFileSystem($uploadedFile->getPathname());
            $md5Hash = $fileHash->getString();
        }

        try {
            $fileUrl = $this->s3Service->uploadFile(
                S3Buckets::MEMBERSHIP_FILES_BUCKET,
                $fileContent,
                $objectKey,
                $contentType
            );

            $file->setName($uniqueFilename);
            $file->setOriginalFilename($originalFilename);
            $file->setObjectKey($objectKey);
            $file->setContentType($contentType);
            $file->setMimeType($contentType);
            $file->setFileSize($fileSize);
            $file->setFileType($fileType);
            $file->setUrl($fileUrl);

            if (null !== $md5Hash) {
                $file->setMd5Hash($md5Hash);
            }

            $this->em->flush();

            return ReplaceFileResponseDTO::fromEntity($file);
        } catch (\Exception $e) {
            throw new FileReplacementException('Failed to replace file: '.$e->getMessage(), 0, $e);
        }
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
            ++$counter;
        } while ($this->doesFileNameExist($newFilename, $parentFolder) && $counter < 1000); // Safety limit

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
