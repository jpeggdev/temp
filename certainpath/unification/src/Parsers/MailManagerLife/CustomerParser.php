<?php

namespace App\Parsers\MailManagerLife;

use App\ValueObjects\CustomerObject;
use App\ValueObjects\ProspectObject;

use function App\Functions\app_getDecimal;

class CustomerParser extends MailManagerLifeParser
{
    public function parseRecord(array $record = [ ]): CustomerObject
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

        $lifeval = $record['lifeval'];
        if (!empty($lifeval)) {
            $lifeval = (float) app_getDecimal($lifeval);
        }
        $slamnt = $record['slamnt'];
        if (!empty($slamnt)) {
            $slamnt = (float) app_getDecimal($slamnt);
        }
        $customerObject =  new CustomerObject([
            'companyId' => $this->getCompanyId(),
            'prospect' => $prospectObject,
            'name' => $record['fullname'],
            'legacyFirstInvoicedAt' => (!empty($record['invcdt'])) ? date_create($record['invcdt']) : null,
            'legacyLifetimeValue' => number_format($lifeval ?? 0, 2, '.', ''),
            'legacyFirstSaleAmount' => number_format($slamnt ?? 0.0, 2, '.', ''),
            'legacyCountInvoices' => (int) $record['lifetran'],
            'legacyLastInvoiceNumber' => $record['invcnmbr'],
        ]);

        if ($customerObject->isCustomer()) {
            return $customerObject;
        }

        return new CustomerObject();
    }

    public static function getRequiredHeaders(): array
    {
        return [
            'city',
            'deleted',
            'dlvryaddrs',
            'fullname',
            'invcdt',
            'invcnmbr',
            'lifetran',
            'lifeval',
            'rvntyp',
            'slamnt',
            'state',
            'zip4',
        ];
    }
}
