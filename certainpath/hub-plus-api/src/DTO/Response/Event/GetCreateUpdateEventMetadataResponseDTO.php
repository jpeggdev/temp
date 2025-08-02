<?php

declare(strict_types=1);

namespace App\DTO\Response\Event;

/**
 * @param array<array{id:int,name:string}> $trades
 */
class GetCreateUpdateEventMetadataResponseDTO
{
    public function __construct(
        public array $trades,
    ) {
    }
}
