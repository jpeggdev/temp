<?php

namespace App\Parsers\FieldEdge;

use App\Parsers\Mixins\InvoiceMixin;
use App\ValueObjects\InvoiceObject;

class InvoiceParser extends FieldEdgeParser
{
    use InvoiceMixin;

    public function parseRecord(array $record = [ ]): InvoiceObject
    {
        $description = $record['task'] ?? 'Imported Invoice';
        $total = $record['total'] ?? null;
        $invoicedAt = (!empty($record['date'])) ? date_create_immutable($record['date']) : null;
        $prospectObject = (new ProspectParser($this->company))->parseRecord($record);
        $invoiceObject = new InvoiceObject([
            'companyId' => $this->getCompanyId(),
            'prospect' => $prospectObject,
            'total' => $total,
            'description' => $description,
            'invoiceNumber' => self::getInvoiceNumberFromRecord($record),
            'invoicedAt' => $invoicedAt,
            '_extra' => $record,
        ]);
        $invoiceObject->externalId = $this->getExternalId(
            $invoiceObject->getKey()
        );

        return $invoiceObject;
    }

    public static function getRequiredHeaders(): array
    {
        return [
            'task',
            'total',
            'date'
        ];
    }
}
