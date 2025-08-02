<?php

declare(strict_types=1);

namespace App\DTO\Request\EventFile;

use Symfony\Component\Validator\Constraints as Assert;

readonly class EventFileQueryDTO
{
    public function __construct(
        #[Assert\NotBlank]
        public int $eventId,
        #[Assert\Choice(['other', 'syllabus'])]
        public ?string $fileType = null,
        public ?string $searchTerm = null,
        #[Assert\GreaterThanOrEqual(0)]
        public int $offset = 0,
        #[Assert\GreaterThan(0)]
        public int $limit = 10,
        public ?string $sortBy = null,
        #[Assert\Choice(['ASC', 'DESC'])]
        public ?string $sortOrder = 'DESC',
    ) {
    }
}
