<?php

namespace App\Tests\Unit\ValueObjects;

use App\Exceptions\FileCouldNotBeRetrieved;
use App\Exceptions\StochasticFilePathWasInvalid;
use App\Tests\AppTestCase;
use App\ValueObjects\StochasticFile;

class StochasticFileTest extends AppTestCase
{
    /**
     * @throws StochasticFilePathWasInvalid
     */
    public function testFileFromMasterSpreadsheet(): void
    {
        $sampleFileName = '"C:\DBF Files S3\NA -  Newark DE Boulden Bros MDB 9-5-24.dbf"';
        $stochasticFile = StochasticFile::fromMasterSpreadsheet($sampleFileName);
        self::assertSame(
            'C:\DBF Files S3\NA -  Newark DE Boulden Bros MDB 9-5-24.dbf',
            $stochasticFile->getPathFromSheet()
        );
        self::assertSame(
            'NA -  Newark DE Boulden Bros MDB 9-5-24.dbf',
            $stochasticFile->getFileName()
        );
        self::assertSame(
            's3://stochastic-files/sync/lists/NA -  Newark DE Boulden Bros MDB 9-5-24.dbf',
            $stochasticFile->getS3Uri()
        );

        $cleanerFile = StochasticFile::fromMasterSpreadsheet(
            '"NA -  Newark DE Boulden Bros MDB 9-5-24.dbf"'
        );
        self::assertSame(
            'NA -  Newark DE Boulden Bros MDB 9-5-24.dbf',
            $cleanerFile->getPathFromSheet()
        );
        self::assertSame(
            'NA -  Newark DE Boulden Bros MDB 9-5-24.dbf',
            $cleanerFile->getFileName()
        );
        self::assertSame(
            's3://stochastic-files/sync/lists/NA -  Newark DE Boulden Bros MDB 9-5-24.dbf',
            $cleanerFile->getS3Uri()
        );
    }

    public function testBadData(): void
    {
        $this->expectException(
            StochasticFilePathWasInvalid::class
        );
        StochasticFile::fromMasterSpreadsheet(
            'Multiple files, locations, Janesville WI North, South'
        );
    }
}
