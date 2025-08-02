<?php

namespace App\Parsers\MailManagerLife;

use App\Parsers\Mixins\EmailAddressMixin;
use App\ValueObjects\ProspectObject;

class ProspectParser extends MailManagerLifeParser
{
    use EmailAddressMixin;

    public function parseRecord(array $record = [ ]): ProspectObject
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
            'isDeleted' => (bool)$record['deleted'],
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
            'city',
            'deleted',
            'dlvryaddrs',
            'fullname',
            'state',
            'zip4',
        ];
    }
}
