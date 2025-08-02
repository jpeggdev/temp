<?php

namespace App\Services\Address;

use App\DTO\Request\Address\PatchAddressDTO;
use App\Entity\Address;
use App\Repository\AddressRepository;

readonly class UpdateAddressService
{
    public function __construct(
        private AddressRepository $addressRepository,
    ) {
    }

    public function updateAddress(int $addressId, PatchAddressDTO $dto): Address
    {
        $address = $this->addressRepository->findOneByIdOrFail($addressId);

        if ($dto->doNotMail !== null) {
            $address->setDoNotMail($dto->doNotMail);
        }

        $this->addressRepository->save($address);

        return $address;
    }
}
