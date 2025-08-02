<?php

namespace App\Tests\Unit\Entity;

use App\Entity\Company;
use App\Entity\User;

class CompanyTest extends AbstractEntityTestCase
{
    private Company $company;

    public function setUp(): void
    {
        parent::setUp();
        $this->company = new Company();
    }

    public function testGetterAndSetterForId(): void
    {
        $this->assertNull($this->company->getId());
    }

    public function testGetterAndSetterForIdentifier(): void
    {
        $identifier = 'test_identifier';
        $this->company->setIdentifier($identifier);
        $this->assertEquals($identifier, $this->company->getIdentifier());
    }

    public function testGetterAndSetterForName(): void
    {
        $name = 'Test Company';
        $this->company->setName($name);
        $this->assertEquals($name, $this->company->getName());
    }

    public function testGetExternalIdentifier(): void
    {
        $identifier = 'test_identifier';
        $this->company->setIdentifier($identifier);
        $expectedExternalIdentifier = $identifier . '_' . Company::HOST_SYSTEM_TAG;
        $this->assertEquals($expectedExternalIdentifier, $this->company->getExternalIdentifier());
    }

    public function testAddAndRemoveUser(): void
    {
        $user = new User();
        $this->company->addUser($user);
        $this->assertTrue($this->company->getUsers()->contains($user));

        $this->company->removeUser($user);
        $this->assertFalse($this->company->getUsers()->contains($user));
    }

    public function testTimestampableTraitMethods(): void
    {
        $createdAt = new \DateTimeImmutable();
        $updatedAt = new \DateTimeImmutable();

        $this->company->setCreatedAt($createdAt);
        $this->company->setUpdatedAt($updatedAt);

        $this->assertEquals($createdAt, $this->company->getCreatedAt());
        $this->assertEquals($updatedAt, $this->company->getUpdatedAt());
    }
}
