<?php

namespace App\Tests\Unit\ValueObjects;

use App\Tests\AppTestCase;
use App\ValueObjects\MailPackageObject;
use App\ValueObjects\ProspectObject;
use DateTimeInterface;

class MailPackageObjectTest extends AppTestCase
{
    private MailPackageObject $mailPackageObject;

    public function setUp(): void
    {
        parent::setUp();
        $this->mailPackageObject = new MailPackageObject();
    }

    public function testConstructor(): void
    {
        $valueObject = new MailPackageObject();
        $this->assertJson($valueObject->toJson());

        $prospect = new ProspectObject();
        $prospect->externalId = 'EXT123';
        $data = [
            'name' => 'Test Package',
            'prospect' => $prospect,
            'series' => 'B',
            'companyId' => 3,
        ];

        $mailPackageObject = new MailPackageObject($data);
        $this->assertEquals('Test Package', $mailPackageObject->name);
        $this->assertEquals('EXT123', $mailPackageObject->prospect?->externalId);
        $this->assertEquals('B', $mailPackageObject->series);
        $this->assertEquals(3, $mailPackageObject->companyId);
        $this->assertEquals($prospect, $mailPackageObject->prospect);
    }

    public function testGetTableName(): void
    {
        $this->assertEquals('mail_package', $this->mailPackageObject->getTableName());
    }

    public function testGetTableSequence(): void
    {
        $this->assertEquals('mail_package_id_seq', $this->mailPackageObject->getTableSequence());
    }

    public function testIsValid(): void
    {
        $this->assertFalse($this->mailPackageObject->isValid());

        $this->mailPackageObject->prospect = new ProspectObject();
        $this->mailPackageObject->name = 'Test Package';
        $this->mailPackageObject->prospectId = 123;

        $this->assertTrue($this->mailPackageObject->isValid());
    }

    public function testToArray(): void
    {
        $this->mailPackageObject->_id = 1;
        $this->mailPackageObject->prospect = new ProspectObject();
        $this->mailPackageObject->series = 'A';
        $this->mailPackageObject->name = 'Test Package';
        $this->mailPackageObject->createdAt = new \DateTimeImmutable('2023-01-01 00:00:00');
        $this->mailPackageObject->updatedAt = new \DateTimeImmutable('2023-01-02 00:00:00');

        $expected = [
            'id' => 1,
            'series' => 'A',
            'name' => 'Test Package',
            'created_at' => '2023-01-01 00:00:00',
            'updated_at' => '2023-01-02 00:00:00',
        ];

        $this->assertEquals($expected, $this->mailPackageObject->toArray());
    }

    public function testPopulate(): void
    {
        $result = $this->mailPackageObject->populate();
        $this->assertInstanceOf(MailPackageObject::class, $result);
        $this->assertSame($this->mailPackageObject, $result);
    }

    public function testDefaultPropertyValues(): void
    {
        $package = new MailPackageObject([]);

        // Test string properties default to null
        $this->assertNull($package->name);
        $this->assertNull($package->prospect);
        $this->assertNull($package->prospectId);
        $this->assertNull($package->series);

        // Test numeric properties default values
        $this->assertEquals(0, $package->companyId);
        $this->assertEquals(0, $package->prospectId);

        // Test inherited properties from AbstractObject
        $this->assertEquals(0, $package->_id);
        $this->assertEquals('', $package->key);
        $this->assertEquals([], $package->_extra);
        $this->assertInstanceOf(DateTimeInterface::class, $package->imported);
        $this->assertInstanceOf(DateTimeInterface::class, $package->createdAt);
        $this->assertInstanceOf(DateTimeInterface::class, $package->updatedAt);
        $this->assertTrue($package->isActive);
        $this->assertFalse($package->isDeleted);
    }
}
