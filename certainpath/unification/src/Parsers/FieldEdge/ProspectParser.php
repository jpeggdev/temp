<?php

namespace App\Parsers\FieldEdge;

use App\Parsers\Mixins\EmailAddressMixin;
use App\ValueObjects\ProspectObject;

class ProspectParser extends FieldEdgeParser
{
    use EmailAddressMixin;

    public function parseRecord(array $record = [ ]): ProspectObject
    {
        $postalCode = (string) $record['zip'];
        $prospectObject = new ProspectObject([
            'company' => $this->getCompanyIdentifier(),
            'companyId' => $this->getCompanyId(),
            'fullName' => $record['customerfullname'],
            'address1' => $record['address1'],
            'address2' => $record['address2'],
            'city' => $record['city'],
            'state' => $record['state'],
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
            'address1',
            'address2',
            'city',
            'customerfullname',
            'state',
            'zip',
        ];
    }
}
