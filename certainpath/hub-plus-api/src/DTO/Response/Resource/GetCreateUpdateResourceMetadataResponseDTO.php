<?php

declare(strict_types=1);

namespace App\DTO\Response\Resource;

class GetCreateUpdateResourceMetadataResponseDTO
{
    public function __construct(
        public array $resourceTags = [],
        public array $resourceCategories = [],
        public array $employeeRoles = [],
        public array $trades = [],
        public array $resourceTypes = [],
    ) {
    }
}
