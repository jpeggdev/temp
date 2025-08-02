<?php

namespace App\Tests\Unit\Parsers\MailManagerLife;

use App\Parsers\MailManagerLife\ProspectParser;
use App\Tests\FunctionalTestCase;
use App\ValueObjects\ProspectObject;
use App\ValueObjects\CompanyObject;

class ProspectParserTest extends FunctionalTestCase
{
    private ProspectParser $parser;

    public function setUp(): void
    {
        parent::setUp();
        $company = $this->getCompanyRepository()->findActiveByIdentifierOrCreate('UNI1');
        $this->parser = new ProspectParser(
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
        ];

        $prospectObject = $this->parser->parseRecord($record);

        $this->assertInstanceOf(ProspectObject::class, $prospectObject);
        $this->assertEquals('John Doe', $prospectObject->fullName);
        $this->assertEquals('123 Main St', $prospectObject->address1);
        $this->assertEquals('Anytown', $prospectObject->city);
        $this->assertEquals('CA', $prospectObject->state);
        $this->assertEquals('12345', $prospectObject->postalCode);
        $this->assertFalse($prospectObject->isDeleted);
        $this->assertTrue($prospectObject->isValid());
    }

    public function testGetRequiredHeaders(): void
    {
        $expectedHeaders = [
            'city',
            'deleted',
            'dlvryaddrs',
            'fullname',
            'state',
            'zip4',
        ];

        $this->assertEquals($expectedHeaders, ProspectParser::getRequiredHeaders());
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
        ];

        $prospectObject = $this->parser->parseRecord($record);

        $this->assertNotEmpty($prospectObject->externalId);
        $this->assertNotEmpty($prospectObject->toJson());
        $this->assertIsArray($prospectObject->_extra);
        $this->assertEquals($record, $prospectObject->_extra);
    }

    public function testParseRecordWithDeletedStatus(): void
    {
        $record = [
            'fullname' => 'John Doe',
            'dlvryaddrs' => '123 Main St',
            'city' => 'Anytown',
            'state' => 'CA',
            'zip4' => '12345',
            'deleted' => '1',
        ];

        $prospectObject = $this->parser->parseRecord($record);

        $this->assertTrue($prospectObject->isDeleted);
        $this->assertTrue($prospectObject->isValid());
    }

    public function testParseRecordGeneratesConsistentExternalId(): void
    {
        $record = [
            'fullname' => 'John Doe',
            'dlvryaddrs' => '123 Main St',
            'city' => 'Anytown',
            'state' => 'CA',
            'zip4' => '12345',
            'deleted' => '0',
        ];

        $firstProspect = $this->parser->parseRecord($record);
        $secondProspect = $this->parser->parseRecord($record);

        $this->assertEquals($firstProspect->externalId, $secondProspect->externalId);
        $this->assertEquals($firstProspect->getKey(), $secondProspect->getKey());
    }
}
