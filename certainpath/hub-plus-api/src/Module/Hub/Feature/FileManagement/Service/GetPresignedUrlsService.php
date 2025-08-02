<?php

declare(strict_types=1);

namespace App\Module\Hub\Feature\FileManagement\Service;

use App\Module\Hub\Feature\FileManagement\DTO\Request\GetPresignedUrlsRequestDTO;
use App\Module\Hub\Feature\FileManagement\DTO\Response\GetPresignedUrlsResponseDTO;
use App\Module\Hub\Feature\FileManagement\Exception\NodeOperationException;
use App\Repository\FileRepository;
use App\Service\AmazonS3Service;

readonly class GetPresignedUrlsService
{
    public function __construct(
        private FileRepository $fileRepository,
        private AmazonS3Service $amazonS3Service,
    ) {
    }

    public function getPresignedUrls(GetPresignedUrlsRequestDTO $dto): GetPresignedUrlsResponseDTO
    {
        if (empty($dto->fileUuids)) {
            return new GetPresignedUrlsResponseDTO([]);
        }

        $files = $this->fileRepository->findByUuids($dto->fileUuids);

        $fileMap = [];
        foreach ($files as $file) {
            $fileMap[$file->getUuid()] = $file;
        }

        foreach ($dto->fileUuids as $fileUuid) {
            if (!isset($fileMap[$fileUuid])) {
                throw new NodeOperationException(sprintf('File with UUID %s not found.', $fileUuid));
            }
        }

        $s3Items = [];
        foreach ($dto->fileUuids as $fileUuid) {
            $file = $fileMap[$fileUuid];
            $s3Items[$fileUuid] = [
                'bucketName' => $file->getBucketName(),
                'objectKey' => $file->getObjectKey(),
            ];
        }

        $presignedUrls = $this->amazonS3Service->generatePresignedUrls($s3Items);

        return new GetPresignedUrlsResponseDTO($presignedUrls);
    }
}
