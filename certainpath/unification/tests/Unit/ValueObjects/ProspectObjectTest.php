<?php

namespace App\Tests\Unit\ValueObjects;

use App\Parsers\GenericIngest\MembersStreamProspectParser;
use App\Tests\AppTestCase;
use App\ValueObjects\CustomerObject;
use App\ValueObjects\ProspectObject;
use DateTime;
use DateTimeInterface;

class ProspectObjectTest extends AppTestCase
{
    private ProspectObject $prospectObject;

    public function setUp(): void
    {
        parent::setUp();
        $this->prospectObject = new ProspectObject();
    }

    public function testActivePropertyLogic(): void
    {
        $record = null;
        $activeTest = $this->getMemberRecordValue($record);
        self::assertFalse($activeTest);

        $record = [];
        $activeTest = $this->getMemberRecordValue($record);
        self::assertFalse($activeTest);

        $record['activemember'] = 'no';
        $activeTest = $this->getMemberRecordValue($record);
        self::assertFalse($activeTest);

        $record['activemember'] = 'YES';
        $activeTest = $this->getMemberRecordValue($record);
        self::assertTrue($activeTest);

        $record['activemember'] = 'yes';
        $activeTest = $this->getMemberRecordValue($record);
        self::assertTrue($activeTest);
    }

    public function testGetTableName(): void
    {
        $this->assertEquals('prospect', $this->prospectObject->getTableName());
    }

    public function testGetTableSequence(): void
    {
        $this->assertEquals('prospect_id_seq', $this->prospectObject->getTableSequence());
    }

    public function testIsValid(): void
    {
        $this->assertFalse($this->prospectObject->isValid());

        $this->prospectObject->company = 'ACC123';
        $this->prospectObject->externalId = 'EXT123';
        $this->prospectObject->companyId = 123;
        $this->prospectObject->fullName = 'John Doe';
        $this->prospectObject->address1 = '123 Main St';
        $this->prospectObject->address2 = 'Suite 100';
        $this->prospectObject->city = 'Anytown';
        $this->prospectObject->state = 'CA';
        $this->prospectObject->postalCode = '12345';

        $this->assertTrue($this->prospectObject->isValid());
    }

    public function testToArray(): void
    {
        $customer = new CustomerObject();
        $this->prospectObject->_id = 1;
        $this->prospectObject->company = 'ACC123';
        $this->prospectObject->companyId = 123;
        $this->prospectObject->externalId = 'EXT456';
        $this->prospectObject->customer = $customer;
        $this->prospectObject->isActive = true;
        $this->prospectObject->isDeleted = false;
        $this->prospectObject->fullName = 'John Doe';
        $this->prospectObject->address1 = '123 Main St';
        $this->prospectObject->address2 = 'Suite 100';
        $this->prospectObject->city = 'Anytown';
        $this->prospectObject->state = 'CA';
        $this->prospectObject->postalCode = '12345';
        $this->prospectObject->postalCodeShort = '123';
        $this->prospectObject->doNotContact = false;
        $this->prospectObject->doNotMail = true;
        $this->prospectObject->createdAt = new DateTime('2023-01-01 00:00:00');
        $this->prospectObject->updatedAt = new DateTime('2023-01-02 00:00:00');

        $expected = [
            'id' => 1,
            'external_id' => 'EXT456',
            'company_id' => 123,
            'is_active' => true,
            'is_deleted' => false,
            'full_name' => 'John Doe',
            'address1' => '123 Main St',
            'address2' => 'Suite 100',
            'city' => 'Anytown',
            'state' => 'CA',
            'postal_code' => '12345',
            'postal_code_short' => '123',
            'do_not_contact' => false,
            'do_not_mail' => true,
            'created_at' => '2023-01-01 00:00:00',
            'updated_at' => '2023-01-02 00:00:00',
            'preferred_address_id' => null,
        ];

        $this->assertEquals($expected, $this->prospectObject->toArray());
    }

    public function testPopulate(): void
    {
        $populated = $this->prospectObject->populate();
        $this->assertInstanceOf(ProspectObject::class, $populated);
        $this->assertSame($this->prospectObject, $populated);
    }

    public function testConstructor(): void
    {
        $prospect = new ProspectObject();
        $this->assertJson($prospect->toJson());

        $data = ['fullName' => 'Jane Doe', 'address1' => '456 Elm St'];
        $prospect = new ProspectObject($data);
        $this->assertEquals('Jane Doe', $prospect->fullName);
        $this->assertEquals('456 Elm St', $prospect->address1);
    }

    public function testDefaultPropertyValues(): void
    {
        $prospect = new ProspectObject([]);

        // Test string properties default to null
        $this->assertIsNumeric($prospect->companyId);
        $this->assertNull($prospect->externalId);
        $this->assertNull($prospect->customerId);
        $this->assertNull($prospect->fullName);
        $this->assertNull($prospect->firstName);
        $this->assertNull($prospect->lastName);
        $this->assertNull($prospect->address1);
        $this->assertNull($prospect->address2);
        $this->assertNull($prospect->city);
        $this->assertNull($prospect->state);
        $this->assertNull($prospect->postalCode);
        $this->assertNull($prospect->postalCodeShort);

        // Test boolean properties default values
        $this->assertFalse($prospect->doNotContact);
        $this->assertFalse($prospect->doNotMail);

        // Test json property initialization
        $this->assertJson($prospect->toJson());

        // Test inherited properties from AbstractObject
        $this->assertEquals(0, $prospect->_id);
        $this->assertEquals('', $prospect->key);
        $this->assertEquals([], $prospect->_extra);
        $this->assertInstanceOf(DateTimeInterface::class, $prospect->imported);
        $this->assertInstanceOf(DateTimeInterface::class, $prospect->createdAt);
        $this->assertInstanceOf(DateTimeInterface::class, $prospect->updatedAt);
        $this->assertTrue($prospect->isActive);
        $this->assertFalse($prospect->isDeleted);
    }

    private function getMemberRecordValue($record): bool
    {
        return
            MembersStreamProspectParser::getMemberRecordValue($record);
    }
}
