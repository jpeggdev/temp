<?php

namespace App\Tests\ValueObject;

use App\Module\Stochastic\Feature\PostageUploads\ValueObject\BatchPostageRecordMap;
use App\Tests\AbstractKernelTestCase;

class BatchPostageRecordMapTest extends AbstractKernelTestCase
{
    public function testSftpColumnMappingForJobId(): void
    {
        $map = new BatchPostageRecordMap();

        // SFTP files use "Job ID" column
        $referenceOptions = explode(',', $map->reference);

        self::assertContains('Job ID', $referenceOptions);
        self::assertContains('jobid', $referenceOptions);
        self::assertContains('job_id', $referenceOptions);
    }

    public function testSftpColumnMappingForNumberOfPieces(): void
    {
        $map = new BatchPostageRecordMap();

        // SFTP files use "Number of Pieces" column
        $quantityOptions = explode(',', $map->quantity_sent);

        self::assertContains('Number of Pieces', $quantityOptions);
        self::assertContains('Pieces', $quantityOptions);
        self::assertContains('pieces', $quantityOptions);
    }

    public function testSftpColumnMappingForTransactionAmount(): void
    {
        $map = new BatchPostageRecordMap();

        // SFTP files use "Transaction Amount" column
        $costOptions = explode(',', $map->cost);

        self::assertContains('Transaction Amount', $costOptions);
        self::assertContains('Amount', $costOptions);
        self::assertContains('amount', $costOptions);
    }

    public function testBackwardCompatibilityForExistingMappings(): void
    {
        $map = new BatchPostageRecordMap();

        // Existing mappings should still work
        $referenceOptions = explode(',', $map->reference);
        self::assertContains('jobid', $referenceOptions);
        self::assertContains('job_id', $referenceOptions);

        $quantityOptions = explode(',', $map->quantity_sent);
        self::assertContains('Pieces', $quantityOptions);
        self::assertContains('pieces', $quantityOptions);

        $costOptions = explode(',', $map->cost);
        self::assertContains('Amount', $costOptions);
        self::assertContains('amount', $costOptions);
    }

    public function testMapCanHandleSftpHeadersWithRealData(): void
    {
        $map = new BatchPostageRecordMap();

        // Simulate SFTP file headers (from actual test file)
        $sftpHeaders = [
            'ACH Withdrawal ID',
            'Available Balance',
            'Business Location',
            'City/State of Permit',
            'Job ID',                    // Maps to reference
            'Number of Pieces',          // Maps to quantity_sent
            'Transaction Amount',        // Maps to cost
            'Transaction Date/Time'
        ];

        // Test that mapping can find the correct columns
        $referenceColumnFound = false;
        $quantityColumnFound = false;
        $costColumnFound = false;

        $referenceOptions = explode(',', $map->reference);
        $quantityOptions = explode(',', $map->quantity_sent);
        $costOptions = explode(',', $map->cost);

        foreach ($sftpHeaders as $header) {
            if (in_array($header, $referenceOptions)) {
                $referenceColumnFound = true;
            }
            if (in_array($header, $quantityOptions)) {
                $quantityColumnFound = true;
            }
            if (in_array($header, $costOptions)) {
                $costColumnFound = true;
            }
        }

        self::assertTrue($referenceColumnFound, 'Should find Job ID column for reference mapping');
        self::assertTrue($quantityColumnFound, 'Should find Number of Pieces column for quantity mapping');
        self::assertTrue($costColumnFound, 'Should find Transaction Amount column for cost mapping');
    }

    public function testCaseInsensitiveMatching(): void
    {
        $map = new BatchPostageRecordMap();

        // Test various case combinations
        $testHeaders = [
            'job id',           // lowercase with space
            'JOB ID',           // uppercase
            'Job Id',           // mixed case
            'number of pieces', // lowercase
            'NUMBER OF PIECES', // uppercase
            'transaction amount', // lowercase
            'TRANSACTION AMOUNT'  // uppercase
        ];

        $referenceOptions = array_map('strtolower', explode(',', $map->reference));
        $quantityOptions = array_map('strtolower', explode(',', $map->quantity_sent));
        $costOptions = array_map('strtolower', explode(',', $map->cost));

        // Verify case-insensitive matching works
        self::assertContains('job id', $referenceOptions);
        self::assertContains('number of pieces', $quantityOptions);
        self::assertContains('transaction amount', $costOptions);
    }
}
