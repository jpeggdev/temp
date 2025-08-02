<?php

declare(strict_types=1);

namespace App\Module\CraftMigration\DTO\Request\Resource;

use Symfony\Component\Validator\Constraints as Assert;

class UpdateRelatedResourcesDTO
{
    public function __construct(
        #[Assert\NotBlank(message: 'The slug field cannot be empty')]
        public string $slug,
        #[Assert\Type('array')]
        public array $relatedResourceIds = [],
    ) {
    }
}
