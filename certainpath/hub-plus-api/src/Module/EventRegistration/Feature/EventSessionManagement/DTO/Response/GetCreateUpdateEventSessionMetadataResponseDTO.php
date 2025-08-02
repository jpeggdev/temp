<?php

namespace App\Module\EventRegistration\Feature\EventSessionManagement\DTO\Response;

/**
 * @param array<array{id:int, name:string, identifier:string}> $timezones
 */
class GetCreateUpdateEventSessionMetadataResponseDTO
{
    public function __construct(
        public array $timezones,
    ) {
    }
}
