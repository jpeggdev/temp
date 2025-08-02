<?php

declare(strict_types=1);

namespace App\DTO\Response;

use App\Entity\CampaignFile;

class CampaignFileListResponseDTO
{
    public function __construct(
        public int $id,
        public string $originalFilename,
        public string $bucketName,
        public string $objectKey,
        public string $contentType,
        public ?\DateTimeInterface $createdAt,
        public ?\DateTimeInterface $updatedAt,
    ) {
    }

    public static function fromEntity(CampaignFile $campaignFile): self
    {
        $file = $campaignFile->getFile();

        return new self(
            $campaignFile->getId(),
            $file->getOriginalFilename(),
            $file->getBucketName(),
            $file->getObjectKey(),
            $file->getContentType(),
            $campaignFile->getCreatedAt(),
            $campaignFile->getUpdatedAt()
        );
    }
}
