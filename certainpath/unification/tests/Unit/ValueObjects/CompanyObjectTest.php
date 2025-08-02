<?php

namespace App\Tests\Unit\ValueObjects;

use App\Entity\Company;
use App\Tests\FunctionalTestCase;
use App\ValueObjects\CompanyObject;
use DateTime;

class CompanyObjectTest extends FunctionalTestCase
{
    private CompanyObject $companyObject;

    public function setUp(): void
    {
        parent::setUp();
        $this->companyObject = new CompanyObject();
    }

    public function testConstructor(): void
    {
        $valueObject = new CompanyObject([]);
        $this->assertJson($valueObject->toJson());
    }

    public function testGetTableName(): void
    {
        $this->assertEquals('company', $this->companyObject->getTableName());
    }

    public function testGetTableSequence(): void
    {
        $this->assertEquals('company_id_seq', $this->companyObject->getTableSequence());
    }

    public function testIsValid(): void
    {
        $this->assertFalse($this->companyObject->isValid());

        $this->companyObject->identifier = 'ACC123';
        $this->companyObject->name = 'Test Company';

        $this->assertTrue($this->companyObject->isValid());
    }

    public function testToArray(): void
    {
        $this->companyObject->_id = 1;
        $this->companyObject->identifier = 'ACC123';
        $this->companyObject->name = 'Test Company';
        $this->companyObject->createdAt = new DateTime('2023-01-01 00:00:00');
        $this->companyObject->updatedAt = new DateTime('2023-01-02 00:00:00');

        $expected = [
            'id' => 1,
            'identifier' => 'ACC123',
            'name' => 'Test Company',
            'created_at' => '2023-01-01 00:00:00',
            'updated_at' => '2023-01-02 00:00:00',
        ];

        $this->assertEquals($expected, $this->companyObject->toArray());
    }

    public function testPopulate(): void
    {
        $populated = $this->companyObject->populate();
        $this->assertInstanceOf(CompanyObject::class, $populated);
        $this->assertSame($this->companyObject, $populated);
    }

    public function testMapCompanyEntityToCompanyObject(): void
    {
        $company = (new Company())
            ->setIdentifier($this->faker->word())
            ->setName($this->faker->word())
        ;

        $companyObject = CompanyObject::fromEntity($company);

        $this->assertTrue($companyObject->isValid());
    }

    public function testDefaultPropertyValues(): void
    {
        $company = new CompanyObject([]);

        // Test string properties default to null
        $this->assertNull($company->identifier);
        $this->assertNull($company->name);

        // Test inherited properties from AbstractObject
        $this->assertEquals(0, $company->_id);
        $this->assertEquals('', $company->key);
        $this->assertEquals([], $company->_extra);
        $this->assertInstanceOf(\DateTimeInterface::class, $company->imported);
        $this->assertInstanceOf(\DateTimeInterface::class, $company->createdAt);
        $this->assertInstanceOf(\DateTimeInterface::class, $company->updatedAt);
        $this->assertTrue($company->isActive);
        $this->assertFalse($company->isDeleted);
    }
}
