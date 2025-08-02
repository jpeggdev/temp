<?php

namespace App\Parsers\MailManagerLife;

use App\Parsers\Mixins\InvoiceMixin;
use App\ValueObjects\AddressObject;
use App\ValueObjects\CustomerObject;
use App\ValueObjects\InvoiceObject;
use App\ValueObjects\ProspectObject;

class InvoiceParser extends MailManagerLifeParser
{
    use InvoiceMixin;

    public function parseRecord(array $record = [ ]): InvoiceObject
    {
        $postalCode = (string) $record['zip4'];
        $prospectObject = new ProspectObject([
            'company' => $this->getCompanyIdentifier(),
            'companyId' => $this->getCompanyId(),
            'fullName' => $record['fullname'],
            'address1' => $record['dlvryaddrs'],
            'city' => $record['city'],
            'state' => $record['state'],
            'postalCode' => $postalCode,
        ]);
        $prospectObject->externalId = $this->getExternalId(
            $prospectObject->getKey()
        );

        $customerObject = new CustomerObject([
            'company' => $this->getCompanyIdentifier(),
            'companyId' => $this->getCompanyId(),
            'prospect' => $prospectObject,
            'name' => $record['fullname'],
        ]);

        $addressObject = new AddressObject([
            'company' => $this->getCompanyIdentifier(),
            'companyId' => $this->getCompanyId(),
            'address1' => $record['dlvryaddrs'],
            'city' => $record['city'],
            'stateCode' => $record['state'],
            'postalCode' => $postalCode,
            'customer' => $customerObject,
            'prospect' => $prospectObject,
        ]);

        $total = number_format((float) $record['slamnt'], 2, '.', '');
        $invoicedAt = (!empty($record['invcdt'])) ? date_create($record['invcdt']) : null;
        $isDeleted = (bool) $record['deleted'];
        $invoiceObject = new InvoiceObject([
            'company' => $this->getCompanyIdentifier(),
            'companyId' => $this->getCompanyId(),
            'description' => 'Invoice',
            'total' => $total,
            'invoiceNumber' => self::getInvoiceNumberFromRecord($record),
            'invoicedAt' => $invoicedAt,
            'isDeleted' => $isDeleted,
            'revenueType' => $record['rvntyp'],
            'prospect' => $prospectObject,
            'customer' => $customerObject,
            'address' => $addressObject,
        ]);
        $invoiceObject->externalId = $this->getExternalId(
            $invoiceObject->getKey()
        );

        return $invoiceObject;
    }

    public static function getRequiredHeaders(): array
    {
        return [
            'city',
            'deleted',
            'dlvryaddrs',
            'fullname',
            'invcdt',
            'invcnmbr',
            'rvntyp',
            'slamnt',
            'state',
            'zip4',
        ];
    }
}
