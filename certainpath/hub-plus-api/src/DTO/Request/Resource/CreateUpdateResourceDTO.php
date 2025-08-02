<?php

declare(strict_types=1);

namespace App\DTO\Request\Resource;

use Symfony\Component\Validator\Constraints as Assert;

class CreateUpdateResourceDTO
{
    /**
     * @param CreateResourceContentBlockDTO[] $contentBlocks
     */
    public function __construct(
        #[Assert\NotBlank(message: 'The title field cannot be empty')]
        public string $title,
        #[Assert\NotBlank(message: 'The slug field cannot be empty')]
        public string $slug,
        public ?string $tagline,
        #[Assert\NotBlank(message: 'The description field cannot be empty')]
        public string $description,
        #[Assert\NotNull(message: 'The type field cannot be null')]
        public int $type,
        public ?string $content_url,
        public ?string $thumbnail_url,
        public ?int $thumbnailFileId,
        public ?string $publish_start_date = null,
        public ?string $publish_end_date = null,
        public ?string $legacy_url = null,
        #[Assert\NotNull]
        public bool $is_published = false,
        public ?string $thumbnailFileUuid = null,
        #[Assert\Type('array')]
        public array $tagIds = [],
        #[Assert\Type('array')]
        public array $tradeIds = [],
        #[Assert\Type('array')]
        public array $roleIds = [],
        #[Assert\Type('array')]
        public array $categoryIds = [],
        #[Assert\Type('array')]
        public array $relatedResourceIds = [],
        /**
         * @var CreateResourceContentBlockDTO[]
         */
        #[Assert\Type('array', message: 'contentBlocks must be an array')]
        #[Assert\Valid]
        public array $contentBlocks = [],
    ) {
    }
}
