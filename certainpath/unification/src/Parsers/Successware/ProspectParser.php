<?php

namespace App\Parsers\Successware;

use App\Parsers\{
    Mixins\EmailAddressMixin
};
use App\ValueObjects\ProspectObject;

use function App\Functions\app_coalesceValues;
use function App\Functions\app_formatName;

class ProspectParser extends SuccesswareParser
{
    use EmailAddressMixin;

    public function parseRecord(array $record = [ ]): ProspectObject
    {
        $postalCode = $record['30billingpostalcode'];
        $fullName = app_formatName(
            $record['25billingfirstname'],
            null,
            $record['24billinglastname']
        );

        $companyName = $record['26billingcompanyname'];

        $fullName = app_coalesceValues([
            $fullName,
            $companyName
        ]);

        $prospectObject = new ProspectObject([
            'company' => $this->getCompanyIdentifier(),
            'companyId' => $this->getCompanyId(),
            'fullName' => $fullName,
            'firstName' => $record['25billingfirstname'],
            'lastName' => $record['24billinglastname'],
            'address1' => $record['27billingaddress1'],
            'address2' => null,
            'city' => $record['28billingcity'],
            'state' => $record['29billingstate'],
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
            '24billinglastname',
            '25billingfirstname',
            '26billingcompanyname',
            '27billingaddress1',
            '28billingcity',
            '29billingstate',
            '30billingpostalcode',
        ];
    }
}
