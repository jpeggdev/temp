<?php

declare(strict_types=1);

namespace App\DTO\Response\Resource;

class GetResourceFilterMetaDataResponseDTO
{
    /**
     * @param array<array{id:int,name:string}> $resourceTypes
     * @param array<array{id:int,name:string}> $employeeRoles
     * @param array<array{id:int,name:string}> $trades
     */
    public function __construct(
        public array $resourceTypes,
        public array $employeeRoles,
        public array $trades,
    ) {
    }
}
