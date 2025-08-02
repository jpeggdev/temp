<?php

namespace App\Services\Address;

use App\DTO\Response\AddressesMatchesResponseDTO;
use App\Entity\Address;
use App\Repository\AddressRepository;

readonly class GetAddressesMatchesService
{
    public function __construct(
        private AddressRepository $addressRepository,
    ) {
    }

    public function getMatches(array $addresses): array
    {
        $externalIds = [];
        $addressesToMatch = [];
        $processedExternalIds = [];

        foreach ($addresses as $address) {
            $addressToMatch = (new Address())
                ->setAddress1(strtoupper($address['address1']))
                ->setAddress2(strtoupper($address['address2']))
                ->setCity(strtoupper($address['city']))
                ->setPostalCode($address['zip'])
                ->setStateCode(strtoupper($address['state']));

            $externalId = $addressToMatch->makeExternalId();
            $addressToMatch->setExternalId($externalId);

            if (isset($processedExternalIds[$externalId])) {
                continue;
            }

            $processedExternalIds[$externalId] = true;
            $externalIds[] = $externalId;
            $addressesToMatch[] = $addressToMatch;
        }

        $matchedAddresses = $this->addressRepository->findAllByExternalIds($externalIds);

        $matchedExternalIds = array_map(
            static fn($matchedAddress) => $matchedAddress->getExternalId(),
            $matchedAddresses->toArray()
        );

        $matchedCount = 0;
        $addressesMatchesResponseDTOs = [];

        foreach ($addressesToMatch as $addressToMatch) {
            $isMatched = in_array($addressToMatch->getExternalId(), $matchedExternalIds, true);
            if ($isMatched) {
                $matchedCount++;
            }

            $addressesMatchesResponseDTOs[] = AddressesMatchesResponseDTO::fromEntity($addressToMatch, $isMatched);
        }

        return [
            'addresses' => $addressesMatchesResponseDTOs,
            'matchesCount' => $matchedCount,
        ];
    }
}
