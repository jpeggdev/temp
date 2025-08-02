<?php

namespace App\Services\Location;

use App\DTO\Request\Location\CreateUpdateLocationDTO;
use App\Entity\Location;
use App\Exceptions\NotFoundException\LocationNotFoundException;
use App\Repository\LocationRepository;

readonly class UpdateLocationService
{
    public function __construct(
        private LocationRepository $locationRepository,
    ) {
    }

    /**
     * @throws LocationNotFoundException
     */
    public function updateLocation(int $locationId, CreateUpdateLocationDTO $dto): Location
    {
        $location = $this->locationRepository->findOneByIdOrFail($locationId);

        $location
            ->setName($dto->name)
            ->setDescription($dto->description)
            ->setPostalCodes($dto->postalCodes);

        $this->locationRepository->saveLocation($location);

        return $location;
    }
}
