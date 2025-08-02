<?php

namespace App\Tests\Unit\Entity;

use App\Entity\Company;
use App\Entity\Prospect;

class ProspectEntityTest extends AbstractEntityTestCase
{
    private Prospect $prospect;

    public function setUp(): void
    {
        parent::setUp();
        $this->prospect = new Prospect();
    }

    public function testGetterAndSetterForId(): void
    {
        $this->assertNull($this->prospect->getId());
    }

    public function testGetterAndSetterForFullName(): void
    {
        $fullName = 'John Doe';
        $this->prospect->setFullName($fullName);
        $this->assertEquals($fullName, $this->prospect->getFullName());
    }

    public function testGetterAndSetterForFirstName(): void
    {
        $firstName = 'John';
        $this->prospect->setFirstName($firstName);
        $this->assertEquals($firstName, $this->prospect->getFirstName());
    }

    public function testGetterAndSetterForLastName(): void
    {
        $lastName = 'Doe';
        $this->prospect->setLastName($lastName);
        $this->assertEquals($lastName, $this->prospect->getLastName());
    }

    public function testGetterAndSetterForAddress1(): void
    {
        $address1 = '123 Main St';
        $this->prospect->setAddress1($address1);
        $this->assertEquals($address1, $this->prospect->getAddress1());
    }

    public function testGetterAndSetterForCity(): void
    {
        $city = 'New York';
        $this->prospect->setCity($city);
        $this->assertEquals($city, $this->prospect->getCity());
    }

    public function testGetterAndSetterForState(): void
    {
        $state = 'NY';
        $this->prospect->setState($state);
        $this->assertEquals($state, $this->prospect->getState());
    }

    public function testGetterAndSetterForPostalCode(): void
    {
        $postalCode = '12345';
        $this->prospect->setPostalCode($postalCode);
        $this->assertEquals($postalCode, $this->prospect->getPostalCode());
        $this->assertEquals('12345', $this->prospect->getPostalCodeShort());
    }

    public function testGetterAndSetterForDoNotMail(): void
    {
        $this->assertFalse($this->prospect->isDoNotMail());
        $this->prospect->setDoNotMail(true);
        $this->assertTrue($this->prospect->isDoNotMail());
    }

    public function testGetterAndSetterForDoNotContact(): void
    {
        $this->assertFalse($this->prospect->isDoNotContact());
        $this->prospect->setDoNotContact(true);
        $this->assertTrue($this->prospect->isDoNotContact());
    }

    public function testGetterAndSetterForCompany(): void
    {
        $company = new Company();
        $this->prospect->setCompany($company);
        $this->assertSame($company, $this->prospect->getCompany());
    }

    public function testExternalIdTrait(): void
    {
        $externalId = 'external123';
        $this->prospect->setExternalId($externalId);
        $this->assertEquals($externalId, $this->prospect->getExternalId());
    }

    public function testTimestampableTrait(): void
    {
        $now = new \DateTimeImmutable();
        $this->prospect->setCreatedAt($now);
        $this->prospect->setUpdatedAt($now);

        $this->assertEquals($now, $this->prospect->getCreatedAt());
        $this->assertEquals($now, $this->prospect->getUpdatedAt());
    }
}