<?php

namespace App\Tests\ValueObject;

use App\Exception\CouldNotReadSheet;
use App\Exception\ExcelFileIsCorrupted;
use App\Exception\FieldsAreMissing;
use App\Exception\NoFilePathWasProvided;
use App\Exception\UnsupportedFileTypeException;
use App\Tests\AbstractKernelTestCase;
use App\ValueObject\CampaignProductRecord;
use App\ValueObject\CampaignProductRecordMap;
use App\ValueObject\CampaignProductTaxonomy;
use App\ValueObject\TabularFile;
use League\Csv\Exception;
use League\Csv\SyntaxError;
use League\Csv\UnavailableStream;
use OpenSpout\Common\Exception\IOException;
use OpenSpout\Reader\Exception\ReaderNotOpenedException;

class CampaignProductTaxonomyTest extends AbstractKernelTestCase
{
    /**
     * @throws NoFilePathWasProvided
     * @throws CouldNotReadSheet
     * @throws ExcelFileIsCorrupted
     * @throws UnsupportedFileTypeException
     * @throws Exception
     * @throws SyntaxError
     * @throws UnavailableStream
     * @throws IOException
     * @throws ReaderNotOpenedException
     * @throws FieldsAreMissing
     */
    public function testCsvIntegrityAndTabularBuildout(): void
    {
        $taxonomyFile =
            __DIR__
            .
            '/../Files/campaign-product-taxonomy.csv';
        self::assertFileExists($taxonomyFile);

        $tabularTaxonomy = TabularFile::fromExcelOrCsvFile(
            new CampaignProductRecordMap(),
            $taxonomyFile,
        );

        /** @var CampaignProductRecord[] $records */
        $records = [];
        foreach (
            $tabularTaxonomy->getRowIteratorForColumns(
                $tabularTaxonomy->getHeadersAsArray()
            ) as $row
        ) {
            /** @var CampaignProductRecord $record */
            $record = CampaignProductRecord::fromTabularRecord($row);
            $record->populateFields();
            $records[] = $record;
        }
        self::assertCount(52, $records);

        $arrayRecords = [];
        foreach ($records as $record) {
            $arrayRecords[] = $record->toArray();
        }
        $taxonomyRecords =
            (new CampaignProductTaxonomy())
                ->getTaxonomyForInitialization()
        ;
        foreach ($taxonomyRecords as $index => $taxonomyRecord) {
            unset(
                $taxonomyRecords[$index]['prospectPrice'],
                $taxonomyRecords[$index]['customerPrice']
            );
        }
        foreach ($arrayRecords as $index => $arrayRecord) {
            unset(
                $arrayRecords[$index]['prospectPrice'],
                $arrayRecords[$index]['customerPrice'],
                $arrayRecords[$index]['meta']
            );
            if (empty($arrayRecords[$index]['hasColoredStock'])) {
                $arrayRecords[$index]['hasColoredStock'] = false;
            } else {
                $arrayRecords[$index]['hasColoredStock'] = true;
            }
        }
        self::assertSame(
            $taxonomyRecords,
            $arrayRecords
        );
    }

    /**
     * @throws IOException
     * @throws FieldsAreMissing
     * @throws UnsupportedFileTypeException
     * @throws ExcelFileIsCorrupted
     * @throws UnavailableStream
     * @throws ReaderNotOpenedException
     * @throws CouldNotReadSheet
     * @throws SyntaxError
     * @throws Exception
     * @throws NoFilePathWasProvided
     */
    public function testTaxonomyFromCsv(): void
    {
        $taxonomyFile =
            __DIR__
            .
            '/../Files/campaign-product-taxonomy.csv';
        self::assertFileExists($taxonomyFile);
        $fromStaticArray = new CampaignProductTaxonomy();
        $records = $fromStaticArray->getTaxonomyForInitialization();
        foreach ($records as $index => $record) {
            unset(
                $records[$index]['prospectPrice'],
                $records[$index]['customerPrice']
            );
        }
        $fromCsv = CampaignProductTaxonomy::fromCsv(
            $taxonomyFile
        )->getTaxonomyForInitialization();
        foreach ($fromCsv as $index => $record) {
            unset(
                $fromCsv[$index]['prospectPrice'],
                $fromCsv[$index]['customerPrice']
            );
        }
        self::assertSame(
            $records,
            $fromCsv
        );
    }
}
