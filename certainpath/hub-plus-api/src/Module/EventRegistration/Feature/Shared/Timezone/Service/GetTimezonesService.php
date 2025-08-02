<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\Shared\Timezone\Service;

use App\DTO\Query\PaginationDTO;
use App\Entity\Timezone;
use App\Module\EventRegistration\Feature\Shared\Timezone\DTO\Query\GetTimezonesDTO;
use App\Module\EventRegistration\Feature\Shared\Timezone\DTO\Response\GetTimezoneResponseDTO;
use App\Repository\TimezoneRepository;

readonly class GetTimezonesService
{
    public function __construct(
        private TimezoneRepository $timezoneRepository,
    ) {
    }

    public function getTimezones(
        GetTimezonesDTO $queryDTO,
        PaginationDTO $paginationDTO,
    ): array {
        $timezones = $this->timezoneRepository->findAllByDTO($queryDTO, $paginationDTO);
        $totalCount = $this->timezoneRepository->getTotalCount($queryDTO);

        $timezoneDTOs = array_map(
            static fn (Timezone $timezone) => GetTimezoneResponseDTO::fromEntity($timezone),
            $timezones->toArray()
        );

        return [
            'timezones' => $timezoneDTOs,
            'totalCount' => $totalCount,
        ];
    }
}
