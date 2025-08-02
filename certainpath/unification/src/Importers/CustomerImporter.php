<?php

namespace App\Importers;

use App\ValueObjects\CustomerObject;
use Doctrine\DBAL\Exception;
use JsonException;

class CustomerImporter extends AbstractImporter
{
    protected function importRecords(): bool
    {
        // Initialize Queries
        $prospectQuery = $this->entityManager->getConnection()
            ->createQueryBuilder()
            ->select('
                prospect.id,
                prospect.external_id,
                prospect.customer_id,
                prospect.company_id,
                prospect.full_name,
                prospect.address1,
                prospect.city,
                prospect.state,
                prospect.postal_code,
                prospect.postal_code_short
            ')
            ->from('prospect', 'prospect')
            ->where('prospect.company_id = :companyId')
            ->andWhere('prospect.external_id = :externalId')
            ->orderBy('prospect.id', 'ASC')
            ->setMaxResults(1);

        $recordCounter = 0;
        $totalRecords = count($this->records);
        /** @var CustomerObject $record */
        foreach ($this->records as $record) {
            $recordCounter++;
            if ($recordCounter % 1000 === 0) {
                echo
                    $this->companyObject->identifier
                    . ': '
                    . date('Y-m-d H:i:s')
                    . ' '
                    . 'START: Customer: Processing Record '
                    . $recordCounter . ' of ' . $totalRecords
                    . PHP_EOL;
            }

            // Record Exists Check
            try {
                if (
                    $prospectArr = $prospectQuery->setParameters([
                    'companyId' => $record->companyId,
                    'externalId' => $record->prospect?->externalId
                    ])->fetchAssociative()
                ) {
                    $record->_id = $prospectArr['customer_id'] ?? 0;
                    if ($this->saveRecord($record)) {
                        $this->updateProspectWithCustomer(
                            $prospectArr['id'],
                            $record->_id
                        );
                    }
                }
            } catch (Exception $e) {
                $encodedRecord = 'COULD NOT ENCODE';
                try {
                    $encodedRecord = json_encode(
                        $record->toArray(),
                        JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT
                    );
                } catch (JsonException $e) {
                    $encodedRecord
                        .=
                        ': '
                        . $e->getMessage();
                }
                echo
                    $this->companyObject->identifier
                    . ': '
                    . date('Y-m-d H:i:s')
                    . ' '
                    .
                    'Error Saving Customer Record: '
                    . $encodedRecord
                    . ': '
                    . $e->getMessage()
                    . PHP_EOL;
                throw $e;
            }
            if ($recordCounter % 1000 === 0) {
                echo
                    $this->companyObject->identifier
                    . ': '
                    . date('Y-m-d H:i:s')
                    . ' '
                    . 'END: Customer: Processing Record '
                    . $recordCounter . ' of ' . $totalRecords
                    . PHP_EOL;
            }
        }

        return true;
    }

    /**
     * @throws Exception
     */
    private function updateProspectWithCustomer(int $prospectId, int $customerId): void
    {
        $this->entityManager->getConnection()
            ->createQueryBuilder()
            ->update('prospect', 'p')
            ->set('customer_id', ':customerId')
            ->where('p.id = :prospectId')
            ->setParameters([
                'customerId' => $customerId,
                'prospectId' => $prospectId,
            ])
            ->executeStatement();
    }

    /**
     * @throws Exception
     */
    protected function countRecords(): int
    {
        return $this->entityManager->getConnection()
            ->createQueryBuilder()
            ->select('COUNT(customer.id)')
            ->from('customer', 'customer')
            ->where('customer.company_id = :companyId')
            ->setParameters([
                'companyId' => $this->companyObject->_id
            ])->fetchOne();
    }
}
