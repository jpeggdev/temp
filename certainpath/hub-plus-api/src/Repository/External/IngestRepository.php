<?php

namespace App\Repository\External;

use App\ValueObject\InvoiceRecord;
use App\ValueObject\MemberRecord;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;

readonly class IngestRepository
{
    public function __construct(
        private Connection $genericIngestConnection,
    ) {
    }

    /**
     * @throws Exception
     */
    public function getDatabaseName(): string
    {
        return $this->genericIngestConnection->getDatabase();
    }

    public function isLocalDatabase(): bool
    {
        $params = $this->genericIngestConnection->getParams();
        $host = $params['host'] ?? '';

        return in_array($host, ['localhost', '127.0.0.1', '::1'], true);
    }

    /**
     * @throws Exception
     */
    public function initializeTables(): void
    {
        $this->genericIngestConnection->executeStatement(
            file_get_contents(__DIR__.'/../../../sql/invoices_stream.sql')
        );
        $this->genericIngestConnection->executeStatement(
            file_get_contents(__DIR__.'/../../../sql/members_stream.sql')
        );
        $this->genericIngestConnection->executeStatement(
            file_get_contents(__DIR__.'/../../../sql/prospects_stream.sql')
        );
    }

    /**
     * @throws Exception
     */
    public function dropTables(): void
    {
        $this->genericIngestConnection->executeStatement(
            'DROP TABLE IF EXISTS invoices_stream'
        );
        $this->genericIngestConnection->executeStatement(
            'DROP TABLE IF EXISTS members_stream'
        );
        $this->genericIngestConnection->executeStatement(
            'DROP TABLE IF EXISTS prospects_stream'
        );
    }

    /**
     * @throws Exception
     */
    public function getTableColumns(string $tableName): array
    {
        return $this->genericIngestConnection->createSchemaManager()->listTableColumns($tableName);
    }

    /**
     * @throws Exception
     */
    public function insertInvoiceRecord(
        InvoiceRecord $invoiceRecord,
    ): void {
        $this->genericIngestConnection->insert(
            'invoices_stream',
            $invoiceRecord->toArray()
        );
    }

    /**
     * @throws Exception
     */
    public function insertInvoiceRecords(array $invoiceRecords): void
    {
        $tableName = 'invoices_stream';
        $this->batchInsertRecords($invoiceRecords, $tableName);
    }

    /**
     * @throws Exception
     */
    public function insertMemberRecord(
        MemberRecord $memberRecord,
    ): void {
        $this->genericIngestConnection->insert(
            'members_stream',
            $memberRecord->toArray()
        );
    }

    /**
     * @throws Exception
     */
    public function insertMemberRecords(array $memberRecords): void
    {
        $tableName = 'members_stream';
        $this->batchInsertRecords($memberRecords, $tableName);
    }

    /**
     * @throws Exception
     */
    public function insertProspectRecords(array $prospectRecords): void
    {
        $tableName = 'prospects_stream';
        $this->batchInsertRecords($prospectRecords, $tableName);
    }

    /**
     * @return InvoiceRecord[]
     *
     * @throws Exception
     */
    public function getInvoiceRecordsForTenant(string $tenant): array
    {
        $stmt = $this->genericIngestConnection->prepare(
            'SELECT * FROM invoices_stream WHERE tenant = :tenant'
        );
        $stmt->bindValue('tenant', $tenant);
        $result = $stmt->executeQuery();
        $records = $result->fetchAllAssociative();

        return array_map(
            static function ($record) {
                /** @var InvoiceRecord $invoiceRecord */
                $invoiceRecord = InvoiceRecord::fromDatabaseRecord($record);

                return $invoiceRecord;
            },
            $records
        );
    }

    /**
     * @throws Exception
     */
    public function getMemberRecordsForTenant(string $tenant): array
    {
        $stmt = $this->genericIngestConnection->prepare(
            'SELECT * FROM members_stream WHERE tenant = :tenant'
        );
        $stmt->bindValue('tenant', $tenant);
        $result = $stmt->executeQuery();
        $records = $result->fetchAllAssociative();

        return array_map(
            static function ($record) {
                /** @var MemberRecord $memberRecord */
                $memberRecord = MemberRecord::fromDatabaseRecord($record);

                return $memberRecord;
            },
            $records
        );
    }

    /**
     * @throws Exception
     */
    public function deleteAllRecordsForTenantAndTable(string $tenant, string $tableName): void
    {
        $this->genericIngestConnection->executeStatement(
            "DELETE FROM $tableName where tenant = :tenant",
            ['tenant' => $tenant]
        );
    }

    /**
     * @throws Exception
     */
    private function batchInsertRecords(array $recordsToAdd, string $tableName): void
    {
        if (0 === count($recordsToAdd)) {
            return;
        }
        $values = [];
        $params = [];
        foreach ($recordsToAdd as $index => $recordToAdd) {
            $recordArray = $recordToAdd->toArray();
            $placeholders = [];
            foreach ($recordArray as $key => $value) {
                if (is_string($value)) {
                    if (preg_match('/^\$?(\d{1,3}(,\d{3})*(\.\d{1,2})?)$/', $value, $matches)) {
                        $value = (float) str_replace(',', '', $matches[1]);
                    } elseif ('' === trim($value)) {
                        $value = null; // Convert empty strings to null
                    } else {
                        $value = mb_convert_encoding($value, 'UTF-8', 'UTF-8');
                    }
                }
                $paramKey = $key.$index;
                $placeholders[] = ":$paramKey";
                $params[$paramKey] = $value;
            }
            $values[] = '('.implode(', ', $placeholders).')';
        }
        $sql =
            'INSERT INTO '
            .$tableName
            .' ('.implode(', ', array_keys($recordsToAdd[0]->toArray()))
            .') VALUES '
            .implode(', ', $values);
        $this->genericIngestConnection->executeStatement($sql, $params);
    }
}
