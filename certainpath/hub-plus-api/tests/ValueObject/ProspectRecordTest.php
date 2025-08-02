<?php

namespace App\Tests\ValueObject;

use App\Exception\CouldNotReadSheet;
use App\Exception\FieldsAreMissing;
use App\Exception\NoFilePathWasProvided;
use App\Exception\UnsupportedFileTypeException;
use App\Tests\AbstractKernelTestCase;
use App\ValueObject\ProspectRecord;
use App\ValueObject\ProspectRecordMap;
use App\ValueObject\TabularFile;
use League\Csv\Exception;
use League\Csv\SyntaxError;
use League\Csv\UnavailableStream;

class ProspectRecordTest extends AbstractKernelTestCase
{
    /**
     * @throws NoFilePathWasProvided
     * @throws CouldNotReadSheet
     * @throws UnsupportedFileTypeException
     * @throws Exception
     * @throws SyntaxError
     * @throws UnavailableStream
     * @throws FieldsAreMissing
     * @throws \JsonException
     */
    public function testProspectImport(): void
    {
        $csvFile =
            __DIR__
            .
            '/../Files/ACXIOM 110 prospects.csv';
        self::assertFileExists($csvFile);

        $tabularFile = TabularFile::fromExcelOrCsvFile(
            new ProspectRecordMap(),
            $csvFile
        );
        $prospectRecords = [];
        $records = $tabularFile->getRowIteratorForColumns(
            $tabularFile->getHeadersAsArray()
        );
        $countProspects = 0;
        foreach ($records as $record) {
            $record['tenant'] = 'Test Tenant';
            $record['software'] = 'Test Software';
            $record['version'] = 'Test Version';
            $record['tag'] = 'Test Tag';
            $record['hub_plus_import_id'] = 101;
            $prospectRecord = ProspectRecord::fromTabularRecord($record);
            $prospectRecords[] = $prospectRecord;
            ++$countProspects;
        }
        self::assertSame(
            110,
            $countProspects
        );
        self::assertCount(
            110,
            $prospectRecords
        );
        foreach ($prospectRecords as $theRecord) {
            unset(
                $theRecord->hub_plus_import_id,
                $theRecord
            );
        }
        $this->assertObjectMatchesJsonFile(
            $prospectRecords,
            __DIR__
            .
            '/../Files/ACXIOM 110 prospects.json'
        );
    }
}
