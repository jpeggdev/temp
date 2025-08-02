<?php

namespace App\Parsers\ServiceTitan;

use App\Parsers\{
    Mixins\EmailAddressMixin
};
use App\ValueObjects\ProspectObject;

use function App\Functions\app_lower;

class MembersStreamProspectParser extends ServiceTitanParser
{
    use EmailAddressMixin;

    public function parseRecord(array $record = [ ]): ProspectObject
    {
        $fullName = $record['customer'] ?? $record['customer_name'] ?? null;
        $address1 = $record['street'] ?? null;
        $city = $record['city'] ?? null;
        $state = $record['state'] ?? null;
        $postalCode = $record['zipcode'] ?? $record['zip'] ?? '';
        $isActive = (
            app_lower($record['activemember'] ?? 'yes') === 'yes'
        );

        $prospectObject = new ProspectObject([
            'company' => $this->getCompanyIdentifier(),
            'companyId' => $this->getCompanyId(),
            'fullName' => $fullName,
            'address1' => $address1,
            'city' => $city,
            'state' => $state,
            'postalCode' => $postalCode,
            'isActive' => $isActive,
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
            'customer',
            'street',
            'city',
            'state',
            'zipcode',
            'activemember',
        ];
    }
}
