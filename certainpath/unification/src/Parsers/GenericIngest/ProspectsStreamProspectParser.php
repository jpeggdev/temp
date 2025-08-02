<?php

namespace App\Parsers\GenericIngest;

use App\Parsers\{
    Mixins\EmailAddressMixin
};
use App\ValueObjects\ProspectObject;

class ProspectsStreamProspectParser extends GenericIngestParser
{
    use EmailAddressMixin;

    public function parseRecord(array $record = [ ]): ProspectObject
    {
        $fullName = $record['individualname'] ?? null;
        $firstName = $record['firstname'] ?? null;
        $lastName = $record['lastname'] ?? null;
        $address1  = $record['address'] ?? null;
        $address2  = $record['address2line'] ?? null;
        $city = $record['city'] ?? null;
        $state = $record['state'] ?? null;
        $postalCode = ($record['zip'] . $record['zip4']) ?? null ;

        $prospectObject = new ProspectObject([
            'company' => $this->getCompanyIdentifier(),
            'companyId' => $this->getCompanyId(),
            'fullName' => $fullName,
            'firstName' => $firstName,
            'lastName' => $lastName,
            'address1' => $address1,
            'address2' => $address2,
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
            'id',
            'createdat',
            'tenant',
            'software',
            'version',
            'tag',
            'processed',
            'prefixttl',
            'individualname',
            'firstname',
            'middlename',
            'lastname',
            'address',
            'address2line',
            'city',
            'state',
            'zip',
            'zip4',
            'country',
            'dpbc',
            'confidencecode',
            'ageofindividual',
            'addrtypeind',
            'networthprem',
            'homeownren',
            'hownrenfl',
            'dsfwalksequence',
            'crrt',
            'areacode',
            'phone',
            'phonenospace',
            'phoneflag',
            'yearhomebuilt',
        ];
    }
}
