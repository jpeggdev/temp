<?php

namespace App\Tests\Unit\ValueObjects;

use App\Tests\AppTestCase;
use App\ValueObjects\InvoiceObject;
use App\ValueObjects\CustomerObject;
use App\ValueObjects\ProspectObject;
use App\ValueObjects\AddressObject;
use DateTime;
use DateTimeInterface;

class InvoiceObjectTest extends AppTestCase
{
    private InvoiceObject $invoiceObject;
    private ProspectObject $prospectObject;

    public function setUp(): void
    {
        parent::setUp();
        $this->invoiceObject = new InvoiceObject([
            'total' => '100.00',
            'description' => 'Test Invoice',
            'invoicedAt' => date_create('2024-01-01'),
            'companyId' => 123,
            'customerId' => 456
        ]);

        $this->prospectObject = new ProspectObject([
            'fullName' => 'fullName',
            'address1' => 'address1',
            'address2' => 'address2',
            'city' => 'New York',
            'state' => 'NY',
            'postalCode' => '00001',
        ]);
    }

    public function testConstructor(): void
    {
        $valueObject = new InvoiceObject([]);
        $this->assertJson($valueObject->toJson());
    }

    public function testIsValid(): void
    {
        $this->assertTrue($this->invoiceObject->isValid());

        $invalidInvoice = new InvoiceObject([]);
        $this->assertFalse($invalidInvoice->isValid());
    }

    public function testToArray(): void
    {
        $array = $this->invoiceObject->toArray();

        $this->assertIsArray($array);
        $this->assertEquals('100.00', $array['total']);
        $this->assertEquals('0.00', $array['sub_total']);
        $this->assertEquals('0.00', $array['tax']);
        $this->assertEquals('0.00', $array['balance']);
        $this->assertEquals('Test Invoice', $array['description']);
        $this->assertEquals(456, $array['customer_id']);
        $this->assertArrayHasKey('invoiced_at', $array);
        $this->assertArrayHasKey('created_at', $array);
        $this->assertArrayHasKey('updated_at', $array);
    }

    public function testPopulate(): void
    {
        $this->invoiceObject->prospect = $this->prospectObject;
        $invoice = $this->invoiceObject->populate();

        // Verify the key is generated correctly using the KEY_FIELDS
        $this->assertNotEmpty($invoice->key);
        $this->assertStringContainsString('fullnameaddress1address2newyorkny00001', $invoice->key);
        $this->assertStringContainsString('10000', $invoice->key);
        $this->assertStringContainsString(md5('Test Invoice'), $invoice->key);
        $this->assertStringContainsString('20240101', $invoice->key);
        $this->assertStringContainsString(
            'fullnameaddress1address2newyorkny00001100005f8de45d53eacdc83e3c6f2db54d10b920240101',
            $invoice->key
        );
    }

    public function testDefaultPropertyValues(): void
    {
        $invoice = new InvoiceObject([]);

        // Test numeric properties default values
        $this->assertEquals('0.00', $invoice->total);
        $this->assertEquals('0.00', $invoice->subTotal);
        $this->assertEquals('0.00', $invoice->tax);
        $this->assertEquals('0.00', $invoice->balance);
        $this->assertEquals(0, $invoice->companyId);
        $this->assertEquals(0, $invoice->customerId);

        // Test nullable properties default to null
        $this->assertNull($invoice->description);
        $this->assertNull($invoice->externalId);
        $this->assertNull($invoice->invoicedAt);
        $this->assertNull($invoice->prospect);

        // Test inherited properties from AbstractObject
        $this->assertEquals(0, $invoice->_id);
        $this->assertEquals([], $invoice->_extra);
        $this->assertInstanceOf(DateTimeInterface::class, $invoice->imported);
        $this->assertInstanceOf(DateTimeInterface::class, $invoice->createdAt);
        $this->assertInstanceOf(DateTimeInterface::class, $invoice->updatedAt);
        $this->assertTrue($invoice->isActive);
        $this->assertFalse($invoice->isDeleted);
    }

    public function testIsValidWithRequiredFields(): void
    {
        $this->invoiceObject->invoicedAt = new DateTime();
        $this->invoiceObject->description = 'Test Invoice';
        $this->invoiceObject->total = '100.00';
        $this->invoiceObject->customerId = 1;

        $this->assertTrue($this->invoiceObject->isValid());
    }

    public function testIsInvalidWithMissingRequiredFields(): void
    {
        $invoiceObject = new InvoiceObject([]);
        $this->assertFalse($invoiceObject->isValid());

        $this->invoiceObject->invoicedAt = new DateTime();
        $this->assertFalse($invoiceObject->isValid());

        $this->invoiceObject->description = 'Test Invoice';
        $this->assertFalse($invoiceObject->isValid());

        $this->invoiceObject->total = '100.00';
        $this->assertFalse($invoiceObject->isValid());
    }

    public function testToArrayFormatsAllFields(): void
    {
        $now = new DateTime();
        $this->invoiceObject->_id = 1;
        $this->invoiceObject->companyId = 100;
        $this->invoiceObject->customerId = 200;
        $this->invoiceObject->externalId = 'EXT123';
        $this->invoiceObject->total = '150.00';
        $this->invoiceObject->subTotal = '125.00';
        $this->invoiceObject->tax = '25.00';
        $this->invoiceObject->balance = '50.00';
        $this->invoiceObject->invoiceNumber = 'INV001';
        $this->invoiceObject->revenueType = 'SERVICE';
        $this->invoiceObject->description = 'Test Invoice';
        $this->invoiceObject->invoicedAt = $now;
        $this->invoiceObject->createdAt = $now;
        $this->invoiceObject->updatedAt = $now;

        $array = $this->invoiceObject->toArray();

        $this->assertEquals(1, $array['id']);
        $this->assertEquals(100, $array['company_id']);
        $this->assertEquals(200, $array['customer_id']);
        $this->assertEquals('EXT123', $array['external_id']);
        $this->assertEquals('150.00', $array['total']);
        $this->assertEquals('125.00', $array['sub_total']);
        $this->assertEquals('25.00', $array['tax']);
        $this->assertEquals('50.00', $array['balance']);
        $this->assertEquals('INV001', $array['invoice_number']);
        $this->assertEquals('SERVICE', $array['revenue_type']);
        $this->assertEquals('Test Invoice', $array['description']);
        $this->assertEquals($now->format('Y-m-d H:i:s'), $array['invoiced_at']);
        $this->assertEquals($now->format('Y-m-d H:i:s'), $array['created_at']);
        $this->assertEquals($now->format('Y-m-d H:i:s'), $array['updated_at']);
    }

    public function testPopulateGeneratesConsistentKey(): void
    {
        $prospect = new ProspectObject();
        $prospect->fullName = 'John Doe';

        $this->invoiceObject->prospect = $prospect;
        $this->invoiceObject->total = '100.00';
        $this->invoiceObject->description = 'Test Invoice';
        $this->invoiceObject->invoicedAt = new DateTime('2024-01-01');

        $firstKey = $this->invoiceObject->populate()->getKey();
        $secondKey = $this->invoiceObject->populate()->getKey();

        $this->assertNotEmpty($firstKey);
        $this->assertEquals($firstKey, $secondKey);
    }

    public function testTableNameAndSequence(): void
    {
        $this->assertEquals('invoice', $this->invoiceObject->getTableName());
        $this->assertEquals('invoice_id_seq', $this->invoiceObject->getTableSequence());
    }

    public function testRelatedObjects(): void
    {
        $customer = new CustomerObject();
        $prospect = new ProspectObject();
        $address = new AddressObject();

        $this->invoiceObject->customer = $customer;
        $this->invoiceObject->prospect = $prospect;
        $this->invoiceObject->address = $address;

        $this->assertInstanceOf(CustomerObject::class, $this->invoiceObject->customer);
        $this->assertInstanceOf(ProspectObject::class, $this->invoiceObject->prospect);
        $this->assertInstanceOf(AddressObject::class, $this->invoiceObject->address);
    }
}
