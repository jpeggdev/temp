<?php

declare(strict_types=1);

namespace App\Service\EventTag;

use App\DTO\Response\EventTag\GetEditEventTagResponseDTO;
use App\Entity\EventTag;

readonly class GetEditEventTagService
{
    public function getEditEventTagDetails(
        EventTag $eventTag,
    ): GetEditEventTagResponseDTO {
        return new GetEditEventTagResponseDTO(
            id: $eventTag->getId(),
            name: $eventTag->getName()
        );
    }
}
