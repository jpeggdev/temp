<?php

namespace App\Parsers\GenericIngest;

use App\Parsers\{
    Mixins\EmailAddressMixin
};
use App\ValueObjects\ProspectObject;

use function App\Functions\app_lower;

class MembersStreamProspectParser extends GenericIngestParser
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
        $isActiveMembership = self::getMemberRecordValue($record);
        $version = $record['version'] ?? null;

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
            'isActiveMembership' => $isActiveMembership,
            '_extra' => $record,
            'version' => $version,
        ]);
        $prospectObject->externalId = $this->getExternalId(
            $prospectObject->getKey()
        );

        return $prospectObject;
    }

    public static function getMemberRecordValue($record): bool
    {
        return
            isset($record['activemember'])
            &&
            app_lower($record['activemember']) === 'yes';
    }

    public static function getRequiredHeaders(): array
    {
        return [
            'activemember',
            'city',
            'customerfirstname',
            'customerlastname',
            'customername',
            'state',
            'street',
            'unit',
            'zip',
        ];
    }
}
