<?php

declare(strict_types=1);

namespace App\DTO\Request\Resource;

use Symfony\Component\Validator\Constraints as Assert;

class CreateResourceContentBlockDTO
{
    public function __construct(
        public ?string $id,
        #[Assert\NotBlank(message: 'The type field cannot be empty')]
        public string $type,
        #[Assert\NotBlank(message: 'The content field cannot be empty')]
        public string $content,
        #[Assert\NotNull]
        public int $order_number,
        // No additional constraints, you can adjust as needed
        public ?array $metadata = [],
        // Keeping fileId for backward compatibility
        public ?int $fileId = null,
        // Adding new UUID field
        public ?string $fileUuid = null,
        public ?string $title = null,
        public ?string $short_description = null,
    ) {
    }
}
