<?php

declare(strict_types=1);

namespace App\Service\ResourceTag;

use App\DTO\Response\ResourceTag\GetEditResourceTagResponseDTO;
use App\Entity\ResourceTag;

readonly class GetEditResourceTagService
{
    public function getEditResourceTagDetails(ResourceTag $resourceTag): GetEditResourceTagResponseDTO
    {
        return new GetEditResourceTagResponseDTO(
            id: $resourceTag->getId(),
            name: $resourceTag->getName()
        );
    }
}
