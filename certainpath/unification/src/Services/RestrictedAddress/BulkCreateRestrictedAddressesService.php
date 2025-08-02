<?php

namespace App\Services\RestrictedAddress;

use App\DTO\Request\Address\RestrictedAddressEditDTO;
use App\DTO\Request\RestrictedAddress\BulkCreateRestrictedAddressesDTO;
use App\Entity\RestrictedAddress;
use App\Exceptions\DomainException\RestrictedAddress\RestrictedAddressAlreadyExistsException;
use App\Repository\AddressRepository;
use App\Repository\RestrictedAddressRepository;
use App\Services\Address\AddressService;

readonly class BulkCreateRestrictedAddressesService
{
    public function __construct(
        private AddressService $addressService,
        private AddressRepository $addressRepository,
        private RestrictedAddressRepository $restrictedAddressRepository,
    ) {
    }

    /**
     * @throws RestrictedAddressAlreadyExistsException
     */
    public function bulkCreate(BulkCreateRestrictedAddressesDTO $dto): array
    {
        $addresses = $dto->addresses;
        $newRestrictedAddresses = [];

        foreach ($addresses as $address) {
            if (!$address['isMatched']) {
                continue;
            }

            $externalId = $address['externalId'];
            $matchedAddress = $this->addressRepository->findOneByExternalId($externalId);
            $existingRestrictedAddress = $this->restrictedAddressRepository->findOneByExternalId($externalId);

            if (!$matchedAddress || $existingRestrictedAddress) {
                continue;
            }

            $restrictedAddressEditDTO = new RestrictedAddressEditDTO(
                $matchedAddress->getAddress1(),
                $matchedAddress->getAddress2(),
                $matchedAddress->getCity(),
                $matchedAddress->getStateCode(),
                $matchedAddress->getPostalCode(),
            );

            $newRestrictedAddresses[] = $this->addressService
                ->editRestrictedAddress(new RestrictedAddress(), $restrictedAddressEditDTO);
        }

        return [
            'restrictedAddresses' => $newRestrictedAddresses,
        ];
    }
}
