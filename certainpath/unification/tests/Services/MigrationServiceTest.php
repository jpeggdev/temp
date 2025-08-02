<?php

namespace App\Tests\Services;

use App\Exceptions\FileConverterException;
use App\Exceptions\PartialProcessingException;
use App\Tests\FunctionalTestCase;
use League\Csv\Exception;
use ReflectionException;

class MigrationServiceTest extends FunctionalTestCase
{
    public function testChallengingDbfFileProcessing(): void
    {
        //SM000159
        $fileToTestBad = __DIR__ . '/../Files/One Hour - Atwater CA (6B) ARMS MDB 2-8-222.dbf';
        self::assertFileExists($fileToTestBad);
        //SM000248
        $fileToTestGood = __DIR__ . '/../Files/NA - Tempe AZ Rescue One MDB 8-5-24.dbf';
        self::assertFileExists($fileToTestGood);
        $migrator = $this->getMigrationService();
        self::assertNotNull($migrator);
        self::assertTrue(
            $migrator->isFileValid(
                $fileToTestGood,
                10
            )
        );
        self::assertFalse(
            $migrator->isFileValid(
                $fileToTestBad,
                10
            )
        );
    }

    /**
     * @throws Exception
     * @throws FileConverterException
     * @throws PartialProcessingException
     * @throws ReflectionException
     * @throws \Doctrine\DBAL\Exception
     */
    public function testMigrationServiceGoodFile(): void
    {
        $companyIdentifier = 'SM000248';
        $fileToTestGood = __DIR__ . '/../Files/NA - Tempe AZ Rescue One MDB 8-5-24.dbf';
        self::assertFileExists($fileToTestGood);
        $migrator = $this->getMigrationService();
        $import = $migrator->migrate(
            $companyIdentifier,
            $fileToTestGood,
            'mailmanager',
            'prospects',
            []
        );
    }

    /**
     * @throws Exception
     * @throws FileConverterException
     * @throws ReflectionException
     * @throws PartialProcessingException
     * @throws \Doctrine\DBAL\Exception
     */
    public function testDuplicateRecordMigration(): void
    {
        $companyIdentifier = 'SM000248';
        $fileToTestGood = __DIR__ . '/../Files/NA - Tempe AZ Rescue One MDB 8-5-24.dbf';
        self::assertFileExists($fileToTestGood);
        $migrator = $this->getMigrationService();
        $migrator->migrate(
            $companyIdentifier,
            $fileToTestGood,
            'mailmanager',
            'prospects',
            [],
            3
        );
        $company = $this->getCompanyRepository()
            ->findOneByIdentifier('SM000248')
            ->getId();

        $importedProspects = $this->getProspectRepository()->fetchAllByCompanyId($company);
        self::assertCount(
            3,
            $importedProspects
        );
        $migrator->migrate(
            $companyIdentifier,
            $fileToTestGood,
            'mailmanager',
            'prospects',
            [],
            3
        );

        $company = $this->getCompanyRepository()->findOneByIdentifier('SM000248');
        $importedProspects = $this->getProspectRepository()->fetchAllByCompanyId($company->getId());
        self::assertCount(
            3,
            $importedProspects
        );
    }

    public function testMigrateWithInvalidFilePath(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("[nonexistent.csv] is not a valid file path.");

        $this->getMigrationService()->migrate(
            'test-company',
            'nonexistent.csv',
            'mailmanagerlife',
            'invoices',
            [],
            null,
            null
        );
    }

    public function testMigrateWithInvalidDataSource(): void
    {
        // Create a temporary test file
        $tempFile = tempnam(sys_get_temp_dir(), 'test');
        file_put_contents($tempFile, 'test content');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage("invalid is not a valid data-source.");

        try {
            $this->getMigrationService()->migrate(
                'test-company',
                $tempFile,
                'invalid',
                'invoices',
                [],
                null,
                null
            );
        } finally {
            unlink($tempFile);
        }
    }

    public function testGetConnection(): void
    {
        $result = $this->getMigrationService()->getConnection();
        $this->assertSame($this->entityManager->getConnection(), $result);
    }
}
