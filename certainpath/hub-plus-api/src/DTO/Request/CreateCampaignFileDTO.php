<?php

declare(strict_types=1);

namespace App\DTO\Request;

use Symfony\Component\Validator\Constraints as Assert;

readonly class CreateCampaignFileDTO
{
    public function __construct(
        #[Assert\NotBlank(message: 'Original filename should not be blank.')]
        #[Assert\Length(
            max: 255,
            maxMessage: 'Original filename cannot be longer than {{ limit }} characters.'
        )]
        public string $originalFilename,
        #[Assert\NotBlank(message: 'Bucket name should not be blank.')]
        #[Assert\Length(
            max: 255,
            maxMessage: 'Bucket name cannot be longer than {{ limit }} characters.'
        )]
        public string $bucketName,
        #[Assert\NotBlank(message: 'Object key should not be blank.')]
        #[Assert\Length(
            max: 1024,
            maxMessage: 'Object key cannot be longer than {{ limit }} characters.'
        )]
        public string $objectKey,
        #[Assert\NotBlank(message: 'Content type should not be blank.')]
        #[Assert\Length(
            max: 255,
            maxMessage: 'Content type cannot be longer than {{ limit }} characters.'
        )]
        public string $contentType,
    ) {
    }
}
