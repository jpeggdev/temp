<?php

declare(strict_types=1);

namespace App\DTO\Request\EventFile;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class CreateEventFileDTO
{
    public function __construct(
        public string $fileName,
        #[Assert\NotBlank(message: 'Original filename should not be blank.')]
        #[Assert\Length(
            max: 255,
            maxMessage: 'Original filename cannot be longer than {{ limit }} characters.'
        )]
        public string $originalFileName,
        #[Assert\NotBlank(message: 'File URL should not be blank.')]
        #[Assert\Length(
            max: 255,
            maxMessage: 'File URL cannot be longer than {{ limit }} characters.'
        )]
        public string $fileUrl,
        #[Assert\NotBlank(message: 'File type should not be blank.')]
        #[Assert\Length(
            max: 50,
            maxMessage: 'File type cannot be longer than {{ limit }} characters.'
        )]
        public string $fileType = 'other',
        public ?string $mimeType = null,
        #[Assert\PositiveOrZero(message: 'File size must be zero or positive.')]
        public ?int $fileSize = null,
        #[Assert\Length(
            max: 255,
            maxMessage: 'Bucket name cannot be longer than {{ limit }} characters.'
        )]
        public ?string $bucketName = null,
    ) {
    }
}
