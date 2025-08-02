<?php

namespace App\Importers;

use App\ValueObjects\AddressObject;
use Doctrine\DBAL\Exception;
use JsonException;

class AddressImporter extends AbstractImporter
{
    /**
     * @throws Exception
     */
    protected function importRecords(): bool
    {
        // Initialize Queries
        $addressQuery = $this->entityManager->getConnection()
            ->createQueryBuilder()
            ->select('address.id')
            ->from('address', 'address')
            ->where('address.company_id = :companyId')
            ->andWhere('address.external_id = :externalId')
            ->orderBy('address.id', 'ASC')
            ->setMaxResults(1);

        $prospectQuery = $this->entityManager->getConnection()
            ->createQueryBuilder()
            ->select('prospect.id')
            ->from('prospect', 'prospect')
            ->where('prospect.company_id = :companyId')
            ->andWhere('prospect.external_id = :externalId')
            ->orderBy('prospect.id', 'ASC')
            ->setMaxResults(1);

        $prospectAddressQuery = $this->entityManager->getConnection()
            ->createQueryBuilder()
            ->select('prospect_address.prospect_id, prospect_address.address_id')
            ->from('prospect_address', 'prospect_address')
            ->where('prospect_address.prospect_id = :prospectId')
            ->andWhere('prospect_address.address_id = :addressId')
            ->setMaxResults(1);

        $prospectAddressInsertQuery = $this->entityManager->getConnection()
            ->createQueryBuilder()
            ->insert('prospect_address')
            ->values([
                'prospect_id' => ':prospectId',
                'address_id' => ':addressId',
            ]);

        $recordCounter = 0;
        $totalRecords = count($this->records);
        /** @var AddressObject $record */
        foreach ($this->records as $record) {
            $recordCounter++;
            if ($recordCounter % 1000 === 0) {
                echo
                    $this->companyObject->identifier
                    . ': '
                    . date('Y-m-d H:i:s')
                    . ' '
                    . 'START: Address: Processing Record '
                    . $recordCounter . ' of ' . $totalRecords
                    . PHP_EOL;
            }
            // Record Exists Check
            try {
                if (empty($record->_id)) {
                    $record->_id = $addressQuery->setParameters([
                        'companyId' => $record->companyId,
                        'externalId' => $record->externalId
                    ])->fetchOne();
                }
                $this->saveRecord($record);

                if (
                    $record->prospect?->externalId &&
                    $prospectId = $prospectQuery->setParameters([
                        'companyId' => $record->companyId,
                        'externalId' => $record->prospect?->externalId
                    ])->fetchOne()
                ) {
                    $prospectAddressCount = $prospectAddressQuery->setParameters([
                        'prospectId' => $prospectId,
                        'addressId' => $record->_id
                    ])->fetchOne();

                    if (!$prospectAddressCount) {
                        $prospectAddressInsertQuery
                            ->setParameter('prospectId', $prospectId)
                            ->setParameter('addressId', $record->_id)
                            ->executeStatement();
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
                    'Error Saving Address Record: '
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
                    . 'END: Address: Processing Record '
                    . $recordCounter . ' of ' . $totalRecords
                    . PHP_EOL;
            }
        }

        return true;
    }

    /**
     * @throws Exception
     */
    protected function countRecords(): int
    {
        return $this->entityManager->getConnection()
            ->createQueryBuilder()
            ->select('COUNT(address.id)')
            ->from('address', 'address')
            ->where('address.company_id = :companyId')
            ->setParameters([
                'companyId' => $this->companyObject->_id
            ])->fetchOne();
    }
}
