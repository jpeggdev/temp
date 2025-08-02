<?php

namespace App\Parsers\GenericIngest;

use App\Parsers\Mixins\EmailAddressMixin;
use App\ValueObjects\ProspectObject;

class InvoicesStreamProspectParser extends GenericIngestParser
{
    use EmailAddressMixin;

    public function parseRecord(array $record = [ ]): ProspectObject
    {
        $fullName = $record['customername'] ?? null;
        $firstName = $record['customerfirstname'] ?? null;
        $lastName = $record['customerlastname'] ?? null;
        $street  = $record['street'] ?? null;
        $unit  = $record['unit'] ?? null;
        $city = $record['city'] ?? null;
        $state = $record['state'] ?? null;
        $postalCode = $record['zip'] ?? null ;

        $prospectObject = new ProspectObject([
            'company' => $this->getCompanyIdentifier(),
            'companyId' => $this->getCompanyId(),
            'fullName' => $fullName,
            'firstName' => $firstName,
            'lastName' => $lastName,
            'address1' => $street,
            'address2' => $unit,
            'city' => $city,
            'state' => $state,
            'postalCode' => $postalCode,
            '_extra' => $record,
        ]);
        $prospectObject->externalId = $this->getExternalId(
            $prospectObject->getKey()
        );

        return $prospectObject;
    }

    public static function getRequiredHeaders(): array
    {
        return [
            'customername',
            'customerfirstname',
            'customerlastname',
            'street',
            'unit',
            'city',
            'state',
            'zip',
        ];
    }
}
