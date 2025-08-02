<?php

namespace App\Tests\Unit\Parsers\MailManagerLife;

use App\Parsers\MailManagerLife\CustomerParser;
use App\Tests\FunctionalTestCase;
use App\ValueObjects\CustomerObject;
use App\ValueObjects\CompanyObject;

class CustomerParserTest extends FunctionalTestCase
{
    private CustomerParser $parser;

    public function setUp(): void
    {
        parent::setUp();
        $company = $this->getCompanyRepository()->findActiveByIdentifierOrCreate('UNI1');
        $this->parser = new CustomerParser(
            CompanyObject::fromEntity($company)
        );
    }

    public function testParseRecord(): void
    {
        $record = [
            'fullname' => 'John Doe',
            'dlvryaddrs' => '123 Main St',
            'city' => 'Anytown',
            'state' => 'CA',
            'zip4' => '12345',
            'deleted' => '0',
            'invcdt' => '20170101',
            'lifeval' => '5405.20',
            'slamnt' => '99.99',
            'lifetran' => '5',
            'invcnmbr' => 'INV01',
        ];

        $customerObject = $this->parser->parseRecord($record);
        $customerObject->prospectId = 123;

        $this->assertInstanceOf(CustomerObject::class, $customerObject);
        $this->assertEquals('John Doe', $customerObject->name);
        $this->assertTrue($customerObject->isValid());
    }

    public function testParseRecordWithMissingOptionalFields(): void
    {
        $record = [
            'fullname' => 'John Doe',
            'dlvryaddrs' => '123 Main St',
            'city' => 'Anytown',
            'state' => 'CA',
            'zip4' => '12345',
            'deleted' => '0',
            'invcdt' => '20170101',
            'lifeval' => '5405.20',
            'slamnt' => '99.99',
            'lifetran' => '5',
            'invcnmbr' => 'INV01',
        ];

        $customerObject = $this->parser->parseRecord($record);
        $customerObject->prospectId = 123;

        $this->assertInstanceOf(CustomerObject::class, $customerObject);
        $this->assertTrue($customerObject->isValid());
    }

    public function testParseRecordSetsMetadata(): void
    {
        $record = [
            'fullname' => 'John Doe',
            'dlvryaddrs' => '123 Main St',
            'city' => 'Anytown',
            'state' => 'CA',
            'zip4' => '12345',
            'deleted' => '0',
            'invcdt' => '20170101',
            'lifeval' => '5405.20',
            'slamnt' => '99.99',
            'lifetran' => '5',
            'invcnmbr' => 'INV01',
        ];

        $customerObject = $this->parser->parseRecord($record);
        $customerObject->prospectId = 123;

        $this->assertIsArray($customerObject->_extra);
        $this->assertTrue($customerObject->isValid());
    }
}
