<?php

namespace App\Tests\Unit\ValueObjects;

use App\Tests\AppTestCase;
use App\ValueObjects\CustomerObject;
use DateTimeInterface;

class CustomerObjectTest extends AppTestCase
{
    private CustomerObject $customerObject;

    public function setUp(): void
    {
        $this->customerObject = new CustomerObject([
            'companyId' => 123,
            'prospectId' => 456,
            'name' => 'Test Customer'
        ]);
    }

    public function testConstructor(): void
    {
        $valueObject = new CustomerObject([]);
        $this->assertJson($valueObject->toJson());
    }

    public function testIsValid(): void
    {
        $this->assertTrue($this->customerObject->isValid());

        $invalidCustomer = new CustomerObject([]);
        $this->assertFalse($invalidCustomer->isValid());
    }

    public function testToArray(): void
    {
        $array = $this->customerObject->toArray();

        $this->assertIsArray($array);
        $this->assertEquals('123', $array['company_id']);
        $this->assertEquals('Test Customer', $array['name']);
        $this->assertEquals(0, $array['legacy_count_invoices']);
        $this->assertEquals('0.00', $array['legacy_lifetime_value']);
        $this->assertEquals('0.00', $array['legacy_first_sale_amount']);
    }

    public function testDefaultPropertyValues(): void
    {
        $customer = new CustomerObject([]);

        // Test string properties default to null
        $this->assertIsNumeric($customer->companyId);
        $this->assertNull($customer->prospect);
        $this->assertNull($customer->prospectId);
        $this->assertNull($customer->name);
        $this->assertNull($customer->legacyLastInvoiceNumber);
        $this->assertNull($customer->legacyFirstInvoicedAt);

        // Test boolean properties default values
        $this->assertFalse($customer->hasInstallation);
        $this->assertFalse($customer->hasSubscription);

        // Test numeric properties default values
        $this->assertEquals(0, $customer->legacyCountInvoices);
        $this->assertEquals('0.00', $customer->legacyLifetimeValue);
        $this->assertEquals('0.00', $customer->legacyFirstSaleAmount);

        // Test inherited properties from AbstractObject
        $this->assertEquals(0, $customer->_id);
        $this->assertEquals('', $customer->key);
        $this->assertEquals([], $customer->_extra);
        $this->assertInstanceOf(DateTimeInterface::class, $customer->imported);
        $this->assertInstanceOf(DateTimeInterface::class, $customer->createdAt);
        $this->assertInstanceOf(DateTimeInterface::class, $customer->updatedAt);
        $this->assertTrue($customer->isActive);
        $this->assertFalse($customer->isDeleted);
    }
}
