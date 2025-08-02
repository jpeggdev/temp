<?php

namespace App\Services\Location;

use App\DTO\Request\Location\CreateUpdateLocationDTO;
use App\Entity\Location;
use App\Exceptions\NotFoundException\CompanyNotFoundException;
use App\Repository\CompanyRepository;
use App\Repository\LocationRepository;

readonly class CreateLocationService
{
    public function __construct(
        private CompanyRepository $companyRepository,
        private LocationRepository $locationRepository,
    ) {
    }

    /**
     * @throws CompanyNotFoundException
     */
    public function createLocation(CreateUpdateLocationDTO $dto): Location
    {
        $company = $this->companyRepository->findOneByIdentifierOrFail($dto->companyIdentifier);

        $location = (new Location())
            ->setName($dto->name)
            ->setCompany($company)
            ->setDescription($dto->description)
            ->setPostalCodes($dto->postalCodes);

        $this->locationRepository->saveLocation($location);

        return $location;
    }
}
