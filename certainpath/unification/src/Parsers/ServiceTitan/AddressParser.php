<?php

namespace App\Parsers\ServiceTitan;

use App\Parsers\Mixins\InvoiceMixin;
use App\ValueObjects\AddressObject;
use App\ValueObjects\InvoiceObject;
use App\ValueObjects\ProspectObject;

class AddressParser extends ServiceTitanParser
{
    use InvoiceMixin;

    public function parseRecord(array $record = [ ]): AddressObject
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
        ]);
        $prospectObject->externalId = $this->getExternalId(
            $prospectObject->getKey()
        );
        $addressObject = new AddressObject([
            'company' => $this->getCompanyIdentifier(),
            'companyId' => $this->getCompanyId(),
            'prospect' => $prospectObject,
            'address1' => $street,
            'city' => $city,
            'stateCode' => $state,
            'postalCode' => $postalCode,
            '_extra' => $record,
        ]);
        $addressObject->externalId = $addressObject->getKey();

        return $addressObject;
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
