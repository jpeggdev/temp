<?php

namespace App\Importers;

use App\ValueObjects\ProspectObject;
use Doctrine\DBAL\Exception;
use JsonException;

class ProspectImporter extends AbstractImporter
{
    /**
     * @throws Exception
     */
    protected function importRecords(): bool
    {
        // Initialize Queries
        $prospectQuery = $this->entityManager->getConnection()
            ->createQueryBuilder()
            ->select('prospect.id')
            ->from('prospect', 'prospect')
            ->where('prospect.company_id = :companyId')
            ->andWhere('prospect.external_id = :externalId')
            ->orderBy('prospect.id', 'ASC')
            ->setMaxResults(1);

        $recordCounter = 0;
        $totalRecords = count($this->records);
        /** @var ProspectObject $record */
        foreach ($this->records as $record) {
            $recordCounter++;
            if ($recordCounter % 1000 === 0) {
                echo
                    $this->companyObject->identifier
                    . ': '
                    . date('Y-m-d H:i:s')
                    . ' '
                    . 'START: Prospect: Processing Record '
                    . $recordCounter . ' of ' . $totalRecords
                    . PHP_EOL;
            }
            // Record Exists Check
            try {
                if (empty($record->_id)) {
                    $record->_id = $prospectQuery->setParameters([
                        'companyId' => $record->companyId,
                        'externalId' => $record->externalId
                    ])->fetchOne();
                }
                $this->saveRecord($record);
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
                    'Error Saving Prospect Record: '
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
                    . 'END: Prospect: Processing Record '
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
            ->select('COUNT(prospect.id)')
            ->from('prospect', 'prospect')
            ->where('prospect.company_id = :companyId')
            ->setParameters([
                'companyId' => $this->companyObject->_id
            ])->fetchOne();
    }
}
