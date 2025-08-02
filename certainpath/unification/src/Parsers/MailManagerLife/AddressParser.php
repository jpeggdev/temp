<?php

namespace App\Parsers\MailManagerLife;

use App\ValueObjects\AddressObject;
use App\ValueObjects\ProspectObject;

class AddressParser extends MailManagerLifeParser
{
    public function parseRecord(array $record = [ ]): AddressObject
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

        $addressObject = new AddressObject([
            'company' => $this->getCompanyIdentifier(),
            'companyId' => $this->getCompanyId(),
            'prospect' => $prospectObject,
            'address1' => $record['dlvryaddrs'],
            'city' => $record['city'],
            'stateCode' => $record['state'],
            'postalCode' => $postalCode,
            'yearBuilt' => $record['yearbuilt'],
            'verifiedAt' => date_create(),
            '_extra' => $record,
        ]);
        $addressObject->externalId = $addressObject->getKey();

        return $addressObject;
    }

    public static function getRequiredHeaders(): array
    {
        return [
            'city',
            'deleted',
            'dlvryaddrs',
            'fullname',
            'state',
            'yearbuilt',
            'zip4',
        ];
    }
}
