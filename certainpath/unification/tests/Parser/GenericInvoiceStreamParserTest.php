<?php

namespace App\Tests\Parser;

use App\Parsers\GenericIngest\InvoiceStreamInvoiceParser;
use App\Tests\FunctionalTestCase;

class GenericInvoiceStreamParserTest extends FunctionalTestCase
{
    public function testInvoiceAtField(): void
    {
        $record = null;
        self::assertNull(
            InvoiceStreamInvoiceParser::getInvoicedAtValue($record)
        );
        $record = [];
        self::assertNull(
            InvoiceStreamInvoiceParser::getInvoicedAtValue($record)
        );
        $record['firstappointment'] = null;
        self::assertNull(
            InvoiceStreamInvoiceParser::getInvoicedAtValue($record)
        );
        $record['firstappointment'] = '';
        self::assertNull(
            InvoiceStreamInvoiceParser::getInvoicedAtValue($record)
        );
        $record['firstappointment'] = 'DERP';
        self::assertNull(
            InvoiceStreamInvoiceParser::getInvoicedAtValue($record)
        );

        $record['firstappointment'] = '1534291200';
        $parsedDate = InvoiceStreamInvoiceParser::getInvoicedAtValue($record);
        self::assertInstanceOf(\DateTimeImmutable::class, $parsedDate);

        $formattedDate = $parsedDate->format('Y-m-d H:i:s');
        self::assertEquals('2018-08-15 00:00:00', $formattedDate);
    }
}
