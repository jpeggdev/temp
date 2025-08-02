<?php

declare(strict_types=1);

namespace App\Service\File;

use App\Constants\S3Buckets;
use App\DTO\Response\UploadTmpFileResponseDTO;
use App\Entity\File;
use App\Entity\FileTmp;
use App\Service\AmazonS3Service;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

final readonly class UploadTmpFileService
{
    public function __construct(
        private AmazonS3Service $s3Service,
        private EntityManagerInterface $em,
    ) {
    }

    /**
     * Uploads the file to S3, creates File + FileTmp with isCommited=false,
     * and returns a DTO { fileId, fileUrl, name, fileUuid }.
     */
    public function uploadTempFile(
        UploadedFile $file,
        ?string $bucketName = null,
        ?string $folderName = null,
    ): UploadTmpFileResponseDTO {
        $bucketName = $bucketName ?? S3Buckets::CERTAIN_PATH_PUBLIC_BUCKET;
        $extension = $file->guessExtension() ?: 'bin';
        $contentType = $file->getMimeType() ?: 'application/octet-stream';
        $originalFilename = $file->getClientOriginalName();
        $fileSize = (int) $file->getSize();

        $uniqueKey = sprintf(
            '%s/%s_%s.%s',
            $folderName ?? 'public',
            uniqid('file_', true),
            md5((string) microtime(true)),
            $extension
        );

        $fileContent = file_get_contents($file->getPathname());
        if (false === $fileContent) {
            throw new FileException('Cannot read uploaded file contents.');
        }

        $fileUrl = $this->s3Service->uploadFile(
            $bucketName,
            $fileContent,
            $uniqueKey,
            $contentType
        );

        $newFile = new File();
        $newFile->setName($originalFilename)
            ->setOriginalFilename($originalFilename)
            ->setBucketName($bucketName)
            ->setObjectKey($uniqueKey)
            ->setContentType($contentType)
            ->setMimeType($contentType)
            ->setFileSize($fileSize)
            ->setFileType($extension)
            ->setUrl($fileUrl);

        $this->em->persist($newFile);

        $fileTmp = new FileTmp();
        $fileTmp->setFile($newFile);
        $fileTmp->setIsCommited(false);

        $this->em->persist($fileTmp);
        $this->em->flush();

        return new UploadTmpFileResponseDTO(
            fileId: $newFile->getId() ?? 0,
            fileUrl: $fileUrl,
            name: $originalFilename,
            fileUuid: $newFile->getUuid(),
        );
    }
}
