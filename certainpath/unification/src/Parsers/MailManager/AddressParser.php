<?php

namespace App\Parsers\MailManager;

use App\ValueObjects\AddressObject;
use App\ValueObjects\ProspectObject;

use function App\Functions\app_upper;

class AddressParser extends MailManagerParser
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

        $isVacant = (app_upper($record['dpvvacant']) === 'Y') ? true : false;
        $addressObject = new AddressObject([
            'company' => $this->getCompanyIdentifier(),
            'companyId' => $this->getCompanyId(),
            'prospect' => $prospectObject,
            'address1' => $record['dlvryaddrs'],
            'city' => $record['city'],
            'stateCode' => $record['state'],
            'postalCode' => $postalCode,
            'isVacant' => $isVacant,
            'yearBuilt' => $record['yearbuilt'],
            'uspsDpvCmra' => $record['dpvcmra'],
            'verifiedAt' => date_create(),
            '_extra' => $record,
        ]);
        $addressObject->externalId = $addressObject->getKey();

        return $addressObject;
    }

    public static function getRequiredHeaders(): array
    {
        return [
            'zip4',
            'fullname',
            'dlvryaddrs',
            'city',
            'state',
            'dlvryaddrs',
            'city',
            'state',
            'dpvvacant',
            'yearbuilt',
            'dpvcmra',
        ];
    }
}
