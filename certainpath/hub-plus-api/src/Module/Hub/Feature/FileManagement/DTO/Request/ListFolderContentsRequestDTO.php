<?php

declare(strict_types=1);

namespace App\Module\Hub\Feature\FileManagement\DTO\Request;

use Symfony\Component\Validator\Constraints as Assert;

readonly class ListFolderContentsRequestDTO
{
    public function __construct(
        #[Assert\Uuid(message: 'Folder UUID must be a valid UUID.')]
        public ?string $folderUuid = null,
        #[Assert\GreaterThanOrEqual(1)]
        #[Assert\LessThanOrEqual(100)]
        public int $limit = 20,
        #[Assert\Choice(choices: ['name', 'fileType', 'updatedAt', 'fileSize'], message: 'Invalid sort field')]
        public string $sortBy = 'name',
        #[Assert\Choice(choices: ['ASC', 'DESC'], message: 'Invalid sort order')]
        public string $sortOrder = 'ASC',
        public ?string $searchTerm = null,
        public ?string $cursor = null,
        #[Assert\All([
            new Assert\Type(type: 'string', message: 'File type must be a string.'),
        ])]
        public array $fileTypes = [],
        #[Assert\All([
            new Assert\Type(type: 'string', message: 'Tag ID must be an integer.'),
        ])]
        public array $tags = [],
    ) {
    }
}
