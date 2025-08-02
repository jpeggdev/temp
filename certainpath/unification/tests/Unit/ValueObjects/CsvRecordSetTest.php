<?php

namespace App\Tests\Unit\ValueObjects;

use App\Parsers\CsvRecordParser;
use App\Parsers\RecordParser;
use App\Tests\AppTestCase;
use JsonException;
use League\Csv\Exception;
use ReflectionException;

class CsvRecordSetTest extends AppTestCase
{
    /**
     * @throws JsonException
     */
    public function testLegacyRecordParser(): void
    {
        [
            $filePath,
            $headersArray,
            $recordsArray
        ] = $this->getReferenceData();

        $parser = new RecordParser(
            $filePath
        );
        $parser->parseRecords(
            3
        );
        self::assertEquals(
            $headersArray,
            $parser->getHeaders()
        );
        self::assertEquals(
            $recordsArray,
            $parser->getRecords()
        );
    }

    /**
     * @throws JsonException
     * @throws Exception
     * @throws ReflectionException
     */
    public function testOptimizedRecordParser(): void
    {
        [
            $filePath,
            $headersArray,
            $recordsArray
        ] = $this->getReferenceData();

        $parser = new CsvRecordParser(
            $filePath
        );
        $parser->parseRecords(
            3
        );
        self::assertEquals(
            $headersArray,
            $parser->getHeaders()
        );
        self::assertEquals(
            $recordsArray,
            $parser->getRecords()
        );
    }

    /**
     * @throws ReflectionException
     * @throws Exception
     * @group largeFiles
     */
    public function testFullSizeCsvOptimizedParsing(): void
    {
        $filePath = __DIR__ . '/../../Files/SM000194/SM000194.csv';
        self::assertFileExists($filePath);
        $parser = new CsvRecordParser(
            $filePath
        );
        $parser->parseRecords();
        self::assertCount(99160, $parser->getRecords());
    }

    /**
     * @return array
     * @throws JsonException
     */
    private function getReferenceData(): array
    {
        $filePath = __DIR__ . '/../../Files/SM000194/SM000194.truncated.csv';
        self::assertFileExists($filePath);
        $headersFile = __DIR__ . '/../../Files/SM000194/headers.json';
        $recordsFile = __DIR__ . '/../../Files/SM000194/records.json';
        $headersArray = json_decode(
            file_get_contents($headersFile),
            true,
            512,
            JSON_THROW_ON_ERROR
        );
        $recordsArray = json_decode(
            file_get_contents($recordsFile),
            true,
            512,
            JSON_THROW_ON_ERROR
        );
        return array($filePath, $headersArray, $recordsArray);
    }
}
