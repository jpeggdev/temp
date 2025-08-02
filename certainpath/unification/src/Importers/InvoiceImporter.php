<?php

namespace App\Importers;

use App\ValueObjects\InvoiceObject;
use Doctrine\DBAL\Exception;
use JsonException;

class InvoiceImporter extends AbstractImporter
{
    /**
     * @throws Exception
     */
    protected function importRecords(): bool
    {
        // Initialize Queries
        $customerQuery = $this->entityManager->getConnection()
            ->createQueryBuilder()
            ->select('customer.id')
            ->from('prospect', 'prospect')
            ->innerJoin(
                'prospect',
                'customer',
                'customer',
                'prospect.customer_id = customer.id'
            )
            ->where('prospect.company_id = :companyId')
            ->andWhere('prospect.external_id = :externalId')
            ->orderBy('prospect.id', 'ASC')
            ->setMaxResults(1);

        $invQueryByCompanyIdExternalId = $this->entityManager->getConnection()
            ->createQueryBuilder()
            ->select('invoice.id')
            ->from('invoice', 'invoice')
            ->where('invoice.company_id = :companyId')
            ->andWhere('invoice.external_id = :externalId')
            ->orderBy('invoice.id', 'ASC')
            ->setMaxResults(1);

        $invQueryByCustomerIdInvoiceNumber = $this->entityManager->getConnection()
            ->createQueryBuilder()
            ->select('invoice.id')
            ->from('invoice', 'invoice')
            ->where('invoice.customer_id = :customerId')
            ->andWhere('invoice.invoice_number = :invoiceNumber')
            ->orderBy('invoice.id', 'ASC')
            ->setMaxResults(1);

        $recordCounter = 0;
        $totalRecords = count($this->records);
        /** @var InvoiceObject $record */
        foreach ($this->records as $record) {
            $recordCounter++;
            if ($recordCounter % 1000 === 0) {
                echo
                    $this->companyObject->identifier
                    . ': '
                    . date('Y-m-d H:i:s')
                    . ' '
                    . 'START: Invoice: Processing Record '
                    . $recordCounter . ' of ' . $totalRecords
                    . PHP_EOL;
            }

            // Record Exists Check
            try {
                if (empty($record->customerId)) {
                    $record->customerId = $customerQuery->setParameters([
                        'companyId' => $record->companyId,
                        'externalId' => $record->prospect?->externalId
                    ])->fetchOne();
                }
                if (empty($record->_id)) {
                    $idFromCustomerIdInvoiceNumber = null;
                    if (
                        !empty($record->customerId) &&
                        !empty($record->invoiceNumber)
                    ) {
                        $idFromCustomerIdInvoiceNumber = $invQueryByCustomerIdInvoiceNumber->setParameters([
                            'customerId' => $record->customerId,
                            'invoiceNumber' => $record->invoiceNumber,
                        ])->fetchOne();
                    }

                    $idFromCompanyIdExternalId = $invQueryByCompanyIdExternalId->setParameters([
                        'companyId' => $record->companyId,
                        'externalId' => $record->externalId
                    ])->fetchOne();

                    $record->_id =
                        $idFromCustomerIdInvoiceNumber ?:
                        $idFromCompanyIdExternalId ?:
                        $record->_id;
                }
                if ($this->getTrade()) {
                    $record->tradeId =
                        $this->getTradeIdForName();
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
                    . 'END: Invoice: Processing Record '
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
            ->select('COUNT(invoice.id)')
            ->from('invoice', 'invoice')
            ->where('invoice.company_id = :companyId')
            ->setParameters([
                'companyId' => $this->companyObject->_id
            ])->fetchOne();
    }
}
