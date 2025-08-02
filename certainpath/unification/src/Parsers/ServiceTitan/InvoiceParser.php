<?php

namespace App\Parsers\ServiceTitan;

use App\Parsers\Mixins\InvoiceMixin;
use App\ValueObjects\InvoiceObject;
use App\ValueObjects\ProspectObject;

class InvoiceParser extends ServiceTitanParser
{
    use InvoiceMixin;

    public function parseRecord(array $record = [ ]): InvoiceObject
    {
        $fullName = $record['customername'] ?? null;
        $street  = $record['street'] ?? null;
        $city = $record['city'] ?? null;
        $state = $record['state'] ?? null;
        $postalCode = $record['zip'] ?? null ;
        $prospectObject = new ProspectObject([
            'company' => $this->getCompanyIdentifier(),
            'companyId' => $this->getCompanyId(),
            'fullName' => $fullName,
            'address1' => $street,
            'city' => $city,
            'state' => $state,
            'postalCode' => $postalCode,
            '_extra' => $record,
        ]);
        $prospectObject->externalId = $this->getExternalId(
            $prospectObject->getKey()
        );

        $description = $record['summary'] ?? 'Imported Invoice';
        $total = number_format(
            $record['total'] ?? null,
            2,
            '.',
            ''
        );
        $invoicedAt =
            (!empty($record['firstappointment']))
                ?
                date_create_immutable($record['firstappointment']) : null;
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
            'jobnumber',
            'invoicenumber',
            'customername',
            'street',
            'city',
            'state',
            'zip',
            'summary',
            'total',
            'firstappointment',
        ];
    }
}
