<?php

namespace App\Tests\ValueObject;

use App\Exception\CouldNotReadSheet;
use App\Exception\FieldsAreMissing;
use App\Exception\NoFilePathWasProvided;
use App\Exception\UnsupportedFileTypeException;
use App\Tests\AbstractKernelTestCase;
use App\ValueObject\MemberRecord;
use App\ValueObject\MemberRecordMap;
use App\ValueObject\TabularFile;
use League\Csv\Exception;
use League\Csv\SyntaxError;
use League\Csv\UnavailableStream;

class MemberRecordTest extends AbstractKernelTestCase
{
    /**
     * @throws UnsupportedFileTypeException
     * @throws UnavailableStream
     * @throws CouldNotReadSheet
     * @throws SyntaxError
     * @throws Exception
     * @throws NoFilePathWasProvided
     * @throws FieldsAreMissing
     */
    public function testMembershipDataImport(): void
    {
        $excelFile =
            __DIR__
            .
            '/../Files/membership-test.xlsx';
        self::assertFileExists($excelFile);
        $this->validateFileImport($excelFile);

        $mixedCaseColumnsExcelFile =
            __DIR__
            .
            '/../Files/membership-test-mixed-case-columns.xlsx';
        self::assertFileExists($mixedCaseColumnsExcelFile);
        $this->validateFileImport($mixedCaseColumnsExcelFile);
    }

    /**
     * @throws CouldNotReadSheet
     * @throws Exception
     * @throws FieldsAreMissing
     * @throws NoFilePathWasProvided
     * @throws SyntaxError
     * @throws UnavailableStream
     * @throws UnsupportedFileTypeException
     */
    private function validateFileImport(string $excelFile): void
    {
        $tabularFile = TabularFile::fromExcelOrCsvFile(
            new MemberRecordMap(),
            $excelFile
        );
        $records = $tabularFile->getRowIteratorForColumns(
            $tabularFile->getHeadersAsArray()
        );
        $count = 0;
        $countMembers = 0;
        foreach ($records as $record) {
            ++$count;
            $record['trade'] = 'Test Trade';
            $record['software'] = 'Test Software';
            $record['tenant'] = 'Test Tenant';
            /* @var MemberRecord $memberRecord */
            $memberRecord = MemberRecord::fromTabularRecord($record);
            try {
                if ($memberRecord instanceof MemberRecord) {
                    $memberRecord->processCustomerNames();
                    $memberRecord->processMembershipType();
                    $memberRecord->validateFieldValues();
                }
            } catch (FieldsAreMissing $e) {
                --$count;
            }
            self::assertArrayHasKey('membership_type', $memberRecord->toArray());
            self::assertArrayHasKey('current_status', $memberRecord->toArray());
            if (
                $memberRecord instanceof MemberRecord
                && $memberRecord->membership_type
                && 'Active' === $memberRecord->current_status
            ) {
                self::assertSame(
                    'Yes',
                    $memberRecord->active_member,
                    $memberRecord->customer_id
                );
                ++$countMembers;
            }
        }
        // 2139 but 4 are invalid.
        // 2135 is NOW the net number of members
        self::assertSame(2135, $count);
        self::assertSame(413, $countMembers);
    }
}
