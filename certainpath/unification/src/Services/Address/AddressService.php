<?php

namespace App\Services\Address;

use App\DTO\Domain\AddressDTO;
use App\DTO\Domain\RestrictedAddressDTO;
use App\DTO\Request\Address\AddressEditDTO;
use App\DTO\Request\Address\PatchAddressDTO;
use App\DTO\Request\Address\RestrictedAddressEditDTO;
use App\Entity\AbstractAddress;
use App\Entity\Address;
use App\Entity\RestrictedAddress;
use App\Exceptions\DomainException\Address\AddressAlreadyExistsException;
use App\Exceptions\DomainException\RestrictedAddress\RestrictedAddressAlreadyExistsException;
use App\Repository\AddressRepository;
use App\Repository\RestrictedAddressRepository;
use App\Services\Address\UpdateAddressService;
use App\Services\AddressVerification\AddressVerificationServiceInterface;
use Doctrine\ORM\EntityManagerInterface;
use function App\Functions\app_lower;
use function App\Functions\app_upper;

readonly class AddressService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private AddressRepository $addressRepository,
        private RestrictedAddressRepository $restrictedAddressRepository,
        private AddressVerificationServiceInterface $addressVerificationService,
    ) {
    }

    public function verifyAndNormalize(AbstractAddress $address): AbstractAddress
    {
        $address->setAddress1(static::sanitizeAddressField($address->getAddress1()));
        $address->setAddress2(static::sanitizeAddressField($address->getAddress2()));
        $normalizedAddress = $this->addressVerificationService->verifyAndNormalize($address);

        if ($address instanceof RestrictedAddress) {
            return $this->restrictedAddressRepository->findOneBy([
                'externalId' => $normalizedAddress->makeExternalId()
            ]) ?? $normalizedAddress;
        }
        if ($address instanceof Address) {
            return $this->addressRepository->findOneBy([
                'company' => $address->getCompany(),
                'externalId' => $normalizedAddress->makeExternalId()
            ]) ?? $normalizedAddress;
        }
        return $address;
    }

    public function resetIsGlobalDoNotMailForAddresses(
        RestrictedAddress $restrictedAddress,
    ): void {
        if ($externalId = $restrictedAddress->getExternalId()) {
            $this->entityManager->createQueryBuilder()
                ->update(Address::class, 'a')
                ->set('a.isGlobalDoNotMail', ':isGlobalDoNotMail')
                ->where('a.externalId = :externalId')
                ->setParameter('isGlobalDoNotMail', false)
                ->setParameter('externalId', $externalId)
                ->getQuery()
                ->execute();
        }
    }

    public function deleteAddress(
        Address $address
    ): void {
        $this->addressRepository->remove($address);
    }

    public function deleteRestrictedAddress(
        RestrictedAddress $restrictedAddress
    ): void {
        $this->resetIsGlobalDoNotMailForAddresses($restrictedAddress);
        $this->restrictedAddressRepository->remove($restrictedAddress);
    }

    /**
     * @throws AddressAlreadyExistsException
     */
    public function editAddress(
        Address $address,
        AddressEditDTO $addressEditDTO
    ): AddressDTO {
        $address->setAddress1($addressEditDTO->address1);
        $address->setAddress2($addressEditDTO->address2);
        $address->setCity($addressEditDTO->city);
        $address->setStateCode($addressEditDTO->stateCode);
        $address->setPostalCode($addressEditDTO->postalCode);
        $address->setCountryCode($addressEditDTO->countryCode);
        $address->setDoNotMail(filter_var(
            $addressEditDTO->isDoNotMail,
            FILTER_VALIDATE_BOOL
        ));

        $existingAddress = $this->addressRepository->findOneByExternalId(
            $address->makeExternalId()
        );

        if (
            $existingAddress &&
            $existingAddress !== $address
        ) {
            throw new AddressAlreadyExistsException();
        }

        $address = $this->addressRepository->saveAddress($address);

        return AddressDTO::fromEntity($address);
    }

    /**
     * @throws RestrictedAddressAlreadyExistsException
     */
    public function editRestrictedAddress(
        RestrictedAddress $restrictedAddress,
        RestrictedAddressEditDTO $restrictedAddressEditDTO
    ): RestrictedAddressDTO {
        $restrictedAddress->setAddress1($restrictedAddressEditDTO->address1);
        $restrictedAddress->setAddress2($restrictedAddressEditDTO->address2);
        $restrictedAddress->setCity($restrictedAddressEditDTO->city);
        $restrictedAddress->setStateCode($restrictedAddressEditDTO->stateCode);
        $restrictedAddress->setPostalCode($restrictedAddressEditDTO->postalCode);
        $restrictedAddress->setCountryCode($restrictedAddressEditDTO->countryCode);

        $existingRestrictedAddress = $this->restrictedAddressRepository->findOneByExternalId(
            $restrictedAddress->makeExternalId()
        );

        if (
            $existingRestrictedAddress &&
            $existingRestrictedAddress !== $restrictedAddress
        ) {
            throw new RestrictedAddressAlreadyExistsException();
        }

        $this->resetIsGlobalDoNotMailForAddresses($restrictedAddress);
        $restrictedAddress = $this->restrictedAddressRepository->saveRestrictedAddress($restrictedAddress);
        $this->propagateDoNotMailForRestrictedAddress($restrictedAddress);

        return RestrictedAddressDTO::fromEntity($restrictedAddress);
    }

    public function propagateDoNotMailForRestrictedAddress(RestrictedAddress $restrictedAddress): void
    {
        if ($externalId = $restrictedAddress->getExternalId()) {
            $this->entityManager->createQueryBuilder()
                ->update(Address::class, 'a')
                ->set('a.isGlobalDoNotMail', ':isGlobalDoNotMail')
                ->where('a.externalId = :externalId')
                ->setParameter('isGlobalDoNotMail', true)
                ->setParameter('externalId', $externalId)
                ->getQuery()
                ->execute();
        }
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

    public static function sanitizeAddressField(?string $addressField): string
    {
        if (!$addressField) {
            return '';
        }
        $addressField = app_lower(
            preg_replace('/[^a-zA-Z0-9 ]/', '', $addressField)
        );

        $replacements = [
            // Street Abbreviations
            ' street ' => ' st ',
            ' road ' => ' rd ',
            ' avenue ' => ' ave ',
            ' boulevard ' => ' blvd ',
            ' lane ' => ' ln ',
            ' drive ' => ' dr ',
            ' court ' => ' ct ',
            ' place ' => ' pl ',
            ' square ' => ' sq ',
            ' terrace ' => ' ter ',
            ' parkway ' => ' pkwy ',
            ' circle ' => ' cir ',
            ' highway ' => ' hwy ',
            ' way ' => ' way ',

            // directional abbreviations
            ' north ' => ' n ',
            ' south ' => ' s ',
            ' east ' => ' e ',
            ' west ' => ' w ',
            ' northeast ' => ' ne ',
            ' northwest ' => ' nw ',
            ' southeast ' => ' se ',
            ' southwest ' => ' sw ',

            // unit abbreviations
            ' apartment ' => ' apt ',
            ' suite ' => ' ste ',
            ' floor ' => ' fl ',
            ' building ' => ' bldg ',
            ' room ' => ' rm ',
            ' department ' => ' dept ',
        ];

        $addressField = ' ' . implode(' ', array_filter(
            explode(' ', $addressField)
        )) . ' ';

        return app_upper(
            trim(
                str_replace(
                    array_keys($replacements),
                    array_values($replacements),
                    $addressField
                )
            )
        );
    }
}
