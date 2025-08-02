<?php

namespace App\Parsers\ServiceTitan;

use App\Parsers\Mixins\InvoiceMixin;
use App\ValueObjects\AddressObject;
use App\ValueObjects\CustomerObject;
use App\ValueObjects\InvoiceObject;
use App\ValueObjects\ProspectObject;

class CustomerParser extends ServiceTitanParser
{
    use InvoiceMixin;

    public function parseRecord(array $record = [ ]): CustomerObject
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

        return new CustomerObject([
            'companyId' => $this->getCompanyId(),
            'prospect' => $prospectObject,
            'name' => $fullName,
        ]);
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
