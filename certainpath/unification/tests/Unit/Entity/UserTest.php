<?php

namespace App\Tests\Unit\Entity;

use App\Entity\User;
use App\Entity\Company;

class UserTest extends AbstractEntityTestCase
{
    private User $user;

    public function setUp(): void
    {
        $this->user = new User();
        parent::setUp();
    }

    public function testGetterAndSetterForId(): void
    {
        $this->assertNull($this->user->getId());
    }

    public function testGetterAndSetterForIdentifier(): void
    {
        $identifier = 'test_user@example.com';
        $this->user->setIdentifier($identifier);
        $this->assertEquals($identifier, $this->user->getIdentifier());
        $this->assertEquals($identifier, $this->user->getUserIdentifier());
        $this->assertEquals($identifier, $this->user->getUsername());
    }

    public function testGetterAndSetterForRoles(): void
    {
        $this->assertEmpty($this->user->getRoles());

        $this->user->addRole('ROLE_USER');
        $this->assertContains('ROLE_USER', $this->user->getRoles());

        $this->user->addAccessRole('ROLE_ADMIN');
        $this->assertContains('ROLE_ADMIN', $this->user->getRoles());

        $this->user->removeAccessRole('ROLE_ADMIN');
        $this->assertNotContains('ROLE_ADMIN', $this->user->getRoles());
    }

    public function testAddAndRemoveCompany(): void
    {
        $company = new Company();
        $this->user->addCompany($company);
        $this->assertTrue($this->user->getCompanies()->contains($company));

        $this->user->removeCompany($company);
        $this->assertFalse($this->user->getCompanies()->contains($company));
    }

    public function testGetCompany(): void
    {
        $this->assertNull($this->user->getCompany());

        $company1 = new Company();
        $company1->setCreatedAt(new \DateTimeImmutable('2023-01-01'));
        $company2 = new Company();
        $company2->setCreatedAt(new \DateTimeImmutable('2023-01-02'));

        $this->user->addCompany($company1);
        $this->user->addCompany($company2);

        $this->assertSame($company1, $this->user->getCompany());
    }

    public function testGetCompanyIdentifiers(): void
    {
        $company1 = new Company();
        $company1->setIdentifier('company1');
        $company2 = new Company();
        $company2->setIdentifier('company2');

        $this->user->addCompany($company1);
        $this->user->addCompany($company2);

        $this->assertEquals(['company1', 'company2'], $this->user->getCompanyIdentifiers());
    }

    public function testUserRoles(): void
    {
        $this->assertFalse($this->user->isSuperAdmin());
        $this->user->makeSuperAdmin();
        $this->assertTrue($this->user->isSuperAdmin());

        $this->assertFalse($this->user->isSystemAdmin());
        $this->user->makeSystemAdmin();
        $this->assertTrue($this->user->isSystemAdmin());

        $this->assertFalse($this->user->isCompanyAdmin());
        $this->user->makeCompanyAdmin();
        $this->assertTrue($this->user->isCompanyAdmin());
    }

    public function testTimestampableTraitMethods(): void
    {
        $createdAt = new \DateTimeImmutable();
        $updatedAt = new \DateTimeImmutable();

        $this->user->setCreatedAt($createdAt);
        $this->user->setUpdatedAt($updatedAt);

        $this->assertEquals($createdAt, $this->user->getCreatedAt());
        $this->assertEquals($updatedAt, $this->user->getUpdatedAt());
    }
}