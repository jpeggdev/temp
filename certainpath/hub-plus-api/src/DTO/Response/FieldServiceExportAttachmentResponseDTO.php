<?php

declare(strict_types=1);

namespace App\DTO\Response;

use App\Entity\FieldServiceExportAttachment;

class FieldServiceExportAttachmentResponseDTO
{
    public function __construct(
        public string $uuid,
        public string $originalFilename,
        public string $bucketName,
        public string $objectKey,
        public string $contentType,
        public string $createdAt,
        public string $updatedAt,
    ) {
    }

    public static function fromFieldServiceExportAttachment(FieldServiceExportAttachment $attachment): self
    {
        return new self(
            $attachment->getUuid(),
            $attachment->getOriginalFilename(),
            $attachment->getBucketName(),
            $attachment->getObjectKey(),
            $attachment->getContentType(),
            $attachment->getCreatedAt()->format('Y-m-d H:i:s'),
            $attachment->getUpdatedAt()->format('Y-m-d H:i:s'),
        );
    }
}
