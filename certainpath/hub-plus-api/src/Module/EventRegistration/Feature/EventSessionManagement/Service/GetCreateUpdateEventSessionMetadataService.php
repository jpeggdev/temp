<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventSessionManagement\Service;

use App\Module\EventRegistration\Feature\EventSessionManagement\DTO\Response\GetCreateUpdateEventSessionMetadataResponseDTO;
use App\Repository\TimezoneRepository;

readonly class GetCreateUpdateEventSessionMetadataService
{
    public function __construct(
        private TimezoneRepository $timezoneRepository,
    ) {
    }

    public function getCreateUpdateEventSessionMetadata(): GetCreateUpdateEventSessionMetadataResponseDTO
    {
        $timezones = $this->timezoneRepository->findAll();

        $timezoneData = array_map(
            static function ($timezone) {
                return [
                    'id' => $timezone->getId(),
                    'name' => $timezone->getName(),
                    'identifier' => $timezone->getIdentifier(),
                ];
            },
            $timezones
        );

        return new GetCreateUpdateEventSessionMetadataResponseDTO(
            timezones: $timezoneData,
        );
    }
}
