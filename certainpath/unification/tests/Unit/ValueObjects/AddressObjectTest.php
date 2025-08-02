<?php

namespace App\Tests\Unit\ValueObjects;

use App\Entity\Address;
use App\Entity\RestrictedAddress;
use App\ValueObjects\AddressObject;
use DateTime;
use DateTimeInterface;
use PHPUnit\Framework\TestCase;

class AddressObjectTest extends TestCase
{
    private AddressObject $addressObject;

    protected function setUp(): void
    {
        $this->addressObject = new AddressObject([
            'companyId' => '123',
            'externalId' => 'ext123',
            'address1' => '123 Main St',
            'address2' => 'Apt 4B',
            'city' => 'Boston',
            'stateCode' => 'MA',
            'postalCode' => '02108'
        ]);
    }

    public function testConstructor(): void
    {
        $valueObject = new AddressObject([]);
        $this->assertJson($valueObject->toJson());
    }

    public function testIsValid(): void
    {
        $this->assertTrue($this->addressObject->isValid());

        $invalidAddress = new AddressObject([]);
        $this->assertFalse($invalidAddress->isValid());
    }

    public function testComputeAddressType(): void
    {
        $this->assertEquals('RESIDENTIAL', $this->addressObject->type);

        $this->addressObject->uspsBusiness = 'Y';
        $this->addressObject->populate();

        $this->assertTrue($this->addressObject->isBusiness);
        $this->assertEquals('COMMERCIAL', $this->addressObject->type);
    }

    public function testComputeIsVacant(): void
    {
        $this->assertFalse($this->addressObject->isVacant);

        $this->addressObject->uspsVacant = 'Y';
        $this->addressObject->populate();

        $this->assertTrue($this->addressObject->isVacant);
    }

    public function testComputeIsVerified(): void
    {
        $this->assertFalse($this->addressObject->isVerified());

        $this->addressObject->verifiedAt = new DateTime();
        $this->addressObject->populate();

        $this->assertTrue($this->addressObject->isVerified());
    }

    public function testToArray(): void
    {
        $array = $this->addressObject->toArray();

        $this->assertIsArray($array);
        $this->assertEquals('123 Main St', $array['address1']);
        $this->assertEquals('Apt 4B', $array['address2']);
        $this->assertEquals('Boston', $array['city']);
        $this->assertEquals('MA', $array['state_code']);
        $this->assertEquals('02108', $array['postal_code']);
    }

    public function testPopulate(): void
    {
        $address = $this->addressObject->populate();

        // Verify the key is generated correctly using the KEY_FIELDS
        $this->assertNotEmpty($address->key);
        $this->assertStringContainsString('123mainst', $address->key);
        $this->assertStringContainsString('apt4b', $address->key);
        $this->assertStringContainsString('boston', $address->key);
        $this->assertStringContainsString('ma', $address->key);
        $this->assertStringContainsString('02108', $address->key);
        $this->assertStringContainsString('123mainstapt4bbostonma02108', $address->key);
    }

    public function testFromAddressEntity(): void
    {
        $mockEntity = $this->createMock(Address::class);
        $mockEntity->method('getId')->willReturn(1);
        $mockEntity->method('getAddress1')->willReturn('123 Main St');
        $mockEntity->method('getAddress2')->willReturn('Apt 4B');
        $mockEntity->method('getCity')->willReturn('Boston');
        $mockEntity->method('getStateCode')->willReturn('MA');
        $mockEntity->method('getPostalCode')->willReturn('02108');
        $mockEntity->method('getCountryCode')->willReturn('US');

        $addressObject = AddressObject::fromEntity($mockEntity);
        $this->assertEquals('123 Main St', $addressObject->address1);
        $this->assertEquals('Apt 4B', $addressObject->address2);
        $this->assertEquals('Boston', $addressObject->city);
        $this->assertEquals('MA', $addressObject->stateCode);
        $this->assertEquals('02108', $addressObject->postalCode);
        $this->assertEquals('US', $addressObject->countryISOCode);
    }

    public function testFromRestrictedAddressEntity(): void
    {
        $mockEntity = $this->createMock(RestrictedAddress::class);
        $mockEntity->method('getId')->willReturn(1);
        $mockEntity->method('getAddress1')->willReturn('123 Main St');
        $mockEntity->method('getAddress2')->willReturn('Apt 4B');
        $mockEntity->method('getCity')->willReturn('Boston');
        $mockEntity->method('getStateCode')->willReturn('MA');
        $mockEntity->method('getPostalCode')->willReturn('02108');
        $mockEntity->method('getCountryCode')->willReturn('US');

        $addressObject = AddressObject::fromEntity($mockEntity);
        $this->assertEquals('123 Main St', $addressObject->address1);
        $this->assertEquals('Apt 4B', $addressObject->address2);
        $this->assertEquals('Boston', $addressObject->city);
        $this->assertEquals('MA', $addressObject->stateCode);
        $this->assertEquals('02108', $addressObject->postalCode);
        $this->assertEquals('US', $addressObject->countryISOCode);
    }

    public function testDefaultPropertyValues(): void
    {
        $address = new AddressObject([]);

        // Test string properties default to null
        $this->assertIsNumeric($address->companyId);
        $this->assertNull($address->externalId);
        $this->assertNull($address->prospect);
        $this->assertNull($address->prospectId);
        $this->assertNull($address->name);
        $this->assertNull($address->address1);
        $this->assertNull($address->address2);
        $this->assertNull($address->city);
        $this->assertNull($address->cityAbbreviation);
        $this->assertNull($address->stateCode);
        $this->assertNull($address->province);
        $this->assertNull($address->country);
        $this->assertNull($address->countryISOCode);
        $this->assertNull($address->uspsStreetAddressAbbreviation);
        $this->assertNull($address->uspsUrbanization);
        $this->assertNull($address->uspsDeliveryPoint);
        $this->assertNull($address->uspsCarrierRoute);
        $this->assertNull($address->uspsDpvConfirmation);
        $this->assertNull($address->uspsDpvCmra);
        $this->assertNull($address->uspsCentralDeliveryPoint);
        $this->assertNull($address->uspsBusiness);
        $this->assertNull($address->uspsVacant);
        $this->assertNull($address->age);
        $this->assertNull($address->postalCode);
        $this->assertNull($address->postalCodeShort);
        $this->assertNull($address->verifiedAt);

        // Test boolean properties default values
        $this->assertFalse($address->isVacant);
        $this->assertFalse($address->isBusiness);
        $this->assertFalse($address->isVerified);

        // Test string properties with default values
        $this->assertEquals('RESIDENTIAL', $address->type);

        // Test inherited properties from AbstractObject
        $this->assertEquals(0, $address->_id);
        $this->assertEquals('', $address->key);
        $this->assertEquals([], $address->_extra);
        $this->assertInstanceOf(DateTimeInterface::class, $address->imported);
        $this->assertInstanceOf(DateTimeInterface::class, $address->createdAt);
        $this->assertInstanceOf(DateTimeInterface::class, $address->updatedAt);
        $this->assertTrue($address->isActive);
        $this->assertFalse($address->isDeleted);
    }
}
