<?php

declare(strict_types=1);

namespace App\DTO\Response;

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

    public static function fromArray(array $data): self
    {
        return new self(
            $data['id'],
            $data['originalFilename'],
            $data['bucketName'],
            $data['objectKey'],
            $data['contentType'],
            isset($data['createdAt']) ? new \DateTimeImmutable($data['createdAt']) : null,
            isset($data['updatedAt']) ? new \DateTimeImmutable($data['updatedAt']) : null
        );
    }
}
