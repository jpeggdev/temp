<?php

namespace App\Services\Location;

use App\Exceptions\NotFoundException\LocationNotFoundException;
use App\Repository\LocationRepository;
use DateTimeImmutable;

readonly class DeleteLocationService
{
    public function __construct(
        private LocationRepository $locationRepository,
    ) {
    }

    /**
     * @throws LocationNotFoundException
     */
    public function deleteLocation(int $locationId): void
    {
        $location = $this->locationRepository->findOneByIdOrFail($locationId);

        $location->setActive(false);
        $location->setDeleted(true);
        $location->setDeletedAt(new DateTimeImmutable());

        $this->locationRepository->save($location);
    }
}
