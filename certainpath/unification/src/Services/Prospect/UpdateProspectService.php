<?php

namespace App\Services\Prospect;

use App\DTO\Request\Address\PatchAddressDTO;
use App\DTO\Request\Prospect\UpdateProspectDoNotMailDTO;
use App\Entity\Prospect;
use App\Repository\ProspectRepository;
use App\Services\Address\UpdateAddressService;

readonly class UpdateProspectService
{
    public function __construct(
        private ProspectRepository $prospectRepository,
        private UpdateAddressService $addressService,
    ) {
    }

    public function updateProspect(int $prospectId, UpdateProspectDoNotMailDTO $dto): Prospect
    {
        $prospect = $this->prospectRepository->findOneByIdOrFail($prospectId);

        if ($dto->doNotMail !== null) {
            $prospect->setDoNotMail($dto->doNotMail);

            $address = $prospect->getPreferredAddress();
            if ($address !== null) {
                $addressDTO = new PatchAddressDTO($dto->doNotMail);
                $this->addressService->updateAddress($address->getId(), $addressDTO);
            }
        }

        $this->prospectRepository->save($prospect);

        return $prospect;
    }
}