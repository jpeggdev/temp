<?php

namespace App\Tests\Unit\Parsers\MailManagerLife;

use App\Parsers\MailManagerLife\AddressParser;
use App\Tests\FunctionalTestCase;
use App\ValueObjects\AddressObject;
use App\ValueObjects\CompanyObject;

class AddressParserTest extends FunctionalTestCase
{
    private AddressParser $parser;

    public function setUp(): void
    {
        parent::setUp();
        $company = $this->getCompanyRepository()->findActiveByIdentifierOrCreate('UNI1');
        $this->parser = new AddressParser(
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
            'yearbuilt' => '1990',
            'deleted' => '0',
        ];

        $addressObject = $this->parser->parseRecord($record);

        $this->assertInstanceOf(AddressObject::class, $addressObject);
        $this->assertEquals('123 Main St', $addressObject->address1);
        $this->assertEquals('Anytown', $addressObject->city);
        $this->assertEquals('CA', $addressObject->stateCode);
        $this->assertEquals('12345', $addressObject->postalCode);
        $this->assertEquals('1990', $addressObject->yearBuilt);
        $this->assertTrue($addressObject->isValid());
    }

    public function testGetRequiredHeaders(): void
    {
        $expectedHeaders = [
            'city',
            'deleted',
            'dlvryaddrs',
            'fullname',
            'state',
            'yearbuilt',
            'zip4',
        ];

        $this->assertEquals($expectedHeaders, AddressParser::getRequiredHeaders());
    }

    public function testParseRecordWithMissingOptionalFields(): void
    {
        $record = [
            'fullname' => 'John Doe',
            'dlvryaddrs' => '123 Main St',
            'city' => 'Anytown',
            'state' => 'CA',
            'zip4' => '12345',
            'yearbuilt' => '',
            'deleted' => '0',
        ];

        $addressObject = $this->parser->parseRecord($record);

        $this->assertInstanceOf(AddressObject::class, $addressObject);
        $this->assertTrue($addressObject->isValid());
        $this->assertEquals('', $addressObject->yearBuilt);
    }

    public function testParseRecordSetsExternalIdAndProspect(): void
    {
        $record = [
            'fullname' => 'John Doe',
            'dlvryaddrs' => '123 Main St',
            'city' => 'Anytown',
            'state' => 'CA',
            'zip4' => '12345',
            'yearbuilt' => '1990',
            'deleted' => '0',
        ];

        $addressObject = $this->parser->parseRecord($record);

        $this->assertTrue($addressObject->isValid());
        $this->assertNotEmpty($addressObject->externalId);
        $this->assertNotEmpty($addressObject->prospect);
    }
}
