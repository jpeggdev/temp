<?php

namespace App\Tests\Repository;

use App\DTO\Domain\RestrictedAddressDTO;
use App\DTO\Request\Address\RestrictedAddressEditDTO;
use App\Entity\RestrictedAddress;
use App\Repository\RestrictedAddressRepository;
use App\Services\Address\AddressService;
use App\Tests\FunctionalTestCase;

class RestrictedAddressRepositoryTest extends FunctionalTestCase
{
    public function testCreateRestrictedAddress(): void
    {
        $repository = $this->getRestrictedAddressRepository();
        $this->assertEquals(0, $repository->count());

        $response = $this->getAddressService()->editRestrictedAddress(
            new RestrictedAddress(),
            $this->getRestrictedAddressEditDTO()
        );
        $this->assertInstanceOf(RestrictedAddressDTO::class, $response);
        $restrictedAddress = $repository->find(1);
        $this->assertInstanceOf(RestrictedAddress::class, $restrictedAddress);
        $this->assertEquals('address1', $response->address1);
        $this->assertEquals('address2', $response->address2);
        $this->assertEquals('city', $response->city);
        $this->assertEquals('ST', $response->stateCode);
        $this->assertEquals('00001', $response->postalCode);
        $this->assertEquals('US', $response->countryCode);
        $this->assertEquals(false, $response->isBusiness);
        $this->assertEquals(false, $response->isVacant);
        $this->assertEquals(false, $response->isVerified);
    }

    public function testEditRestrictedAddress(): void
    {
        $repository = $this->getRestrictedAddressRepository();
        $this->testCreateRestrictedAddress();
        $restrictedAddressEditDTO = $this->getRestrictedAddressEditDTO();
        $restrictedAddress = $repository->find(1);

        $this->assertEquals('address1', $restrictedAddress->getAddress1());

        $restrictedAddressEditDTO->address1 = 'updated-address1';

        $response = $this->getAddressService()->editRestrictedAddress(
            $restrictedAddress,
            $restrictedAddressEditDTO
        );

        $this->assertEquals('updated-address1', $response->address1);

        $restrictedAddress = $repository->find(1);
        $this->assertEquals('updated-address1', $restrictedAddress->getAddress1());
    }

    private function getAddressService(): AddressService
    {
        return $this->getService(
            AddressService::class
        );
    }

    private function getRestrictedAddressRepository(): RestrictedAddressRepository
    {
        return $this->getService(
            RestrictedAddressRepository::class
        );
    }

    private function getRestrictedAddressEditDTO(): RestrictedAddressEditDTO
    {
        return new RestrictedAddressEditDTO(
            'address1',
            'address2',
            'city',
            'ST',
            '00001',
            'US',
            'true',
            'false',
        );
    }
}
