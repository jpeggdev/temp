<?php

namespace App\Tests\Parser;

use App\Entity\Company;
use App\Parsers\CsvRecordParser;
use App\Parsers\GenericIngest\InvoicesStreamProspectParser;
use App\Tests\FunctionalTestCase;
use App\ValueObjects\CompanyObject;
use DateTimeImmutable;
use League\Csv\Exception;
use ReflectionException;

class GenericInvoicesStreamProspectParserTest extends FunctionalTestCase
{
    /**
     * @throws ReflectionException
     * @throws Exception
     */
    public function testJimSmith(): void
    {
        $filePath = __DIR__ . '/../Files/jim-smith-invoices.csv';
        self::assertFileExists($filePath);
        $parser = new CsvRecordParser(
            $filePath
        );
        $parser->parseRecords();
        $records = $parser->getRecords();

        $companyEntity = new Company();
        $companyEntity->setIdentifier('SM1234');
        $companyEntity->setName('Acme Inc.');
        $companyEntity->setActive(true);
        $companyEntity->setUpdatedAt(new DateTimeImmutable());
        $companyEntity->setCreatedAt(new DateTimeImmutable());

        $company = CompanyObject::fromEntity($companyEntity);

        $streamParser = new InvoicesStreamProspectParser(
            $company
        );

        foreach ($records as $record) {
            $prospectObject = $streamParser->parseRecord($record);
            self::assertSame(
                'id.jimsmith1336valleydrjustintx76247',
                $prospectObject->externalId
            );
        }
    }
}
