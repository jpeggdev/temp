<?php

declare(strict_types=1);

namespace App\Module\Hub\Feature\FileManagement\DTO\Request;

use Symfony\Component\Validator\Constraints as Assert;

readonly class BulkDeleteNodesRequestDTO
{
    public function __construct(
        #[Assert\NotBlank(message: 'UUIDs cannot be empty')]
        #[Assert\Count(min: 1, minMessage: 'At least one item must be selected')]
        #[Assert\All([
            new Assert\Uuid(message: 'Invalid UUID format'),
        ])]
        public array $uuids,
    ) {
    }
}
