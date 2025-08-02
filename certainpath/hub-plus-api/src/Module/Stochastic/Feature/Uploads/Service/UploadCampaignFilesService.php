<?php

declare(strict_types=1);

namespace App\Module\Stochastic\Feature\Uploads\Service;

use App\Constants\S3Buckets;
use App\DTO\Request\CreateCampaignFileDTO;
use App\Service\AmazonS3Service;
use App\Service\Unification\CreateCampaignFileService;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class UploadCampaignFilesService
{
    private AmazonS3Service $s3FileUploader;
    private CreateCampaignFileService $createCampaignFileService;
    private const string S3_FOLDER = 'campaign-files';

    public function __construct(
        AmazonS3Service $s3FileUploader,
        CreateCampaignFileService $createCampaignFileService,
    ) {
        $this->s3FileUploader = $s3FileUploader;
        $this->createCampaignFileService = $createCampaignFileService;
    }

    public function uploadAndCreateCampaignFile(int $campaignId, UploadedFile $file): array
    {
        // Get the S3 key and metadata
        [$s3Key, $contentType, $originalFilename] = $this->prepareFileMetadata($campaignId, $file);

        // Upload the file to S3
        $fileContent = file_get_contents($file->getPathname());
        $this->s3FileUploader->uploadFile(S3Buckets::MEMBERSHIP_FILES_BUCKET, $fileContent, $s3Key, $contentType);

        // Create the file DTO
        $fileDto = new CreateCampaignFileDTO(
            originalFilename: $originalFilename,
            bucketName: S3Buckets::MEMBERSHIP_FILES_BUCKET,
            objectKey: $s3Key,
            contentType: $contentType
        );

        // Register the file with the Unification API
        return $this->createCampaignFileService->createCampaignFile($campaignId, $fileDto);
    }

    public function uploadFile(int $campaignId, UploadedFile $file): string
    {
        // Get the S3 key and metadata
        [$s3Key, $contentType] = $this->prepareFileMetadata($campaignId, $file);

        // Upload the file to S3
        $fileContent = file_get_contents($file->getPathname());

        return $this->s3FileUploader->uploadFile(
            S3Buckets::MEMBERSHIP_FILES_BUCKET,
            $fileContent,
            $s3Key,
            $contentType
        );
    }

    /**
     * Helper method to prepare the S3 key and file metadata.
     */
    private function prepareFileMetadata(int $campaignId, UploadedFile $file): array
    {
        $fileExtension = $file->guessExtension() ?: 'bin';
        $s3Key = sprintf(
            '%s/campaign_%d/%s.%s',
            self::S3_FOLDER,
            $campaignId,
            uniqid('file_', true),
            $fileExtension
        );

        $contentType = $file->getMimeType() ?: 'application/octet-stream';
        $originalFilename = $file->getClientOriginalName();

        return [$s3Key, $contentType, $originalFilename];
    }
}
