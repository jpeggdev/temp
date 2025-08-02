<?php

namespace App\Parsers\MailManager;

use App\ValueObjects\MailPackageObject;
use App\ValueObjects\ProspectObject;

use function App\Functions\app_getPostalCodeShort;

class MailPackageParser extends MailManagerParser
{
    public function parseRecord(array $record = [ ]): MailPackageObject
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

        return new MailPackageObject([
            'companyId' => $this->getCompanyId(),
            'prospect' => $prospectObject,
            'series' => $record['series'],
            'name' => $record['name'],
        ]);
    }

    public function parseRecords(array $records = [ ]): bool
    {
        foreach ($records as $record) {
            foreach ($record as $key => $value) {
                if (
                    preg_match('/^pkgmldt.*/', $key) &&
                    !empty($value)
                ) {
                    $mailPackageObject = $this->parseRecord(array_merge($record, [
                        'series' => $key,
                        'name' => $value,
                    ]));

                    $this->addRecord(
                        $mailPackageObject
                    );
                }
            }
        }

        return true;
    }

    public static function getRequiredHeaders(): array
    {
        return [
            'fullname',
            'dlvryaddrs',
            'altrnt1add',
            'city',
            'state',
            'zip4',
            'homeowner',
            'region',
            'verified',
            'phoneflag',
            'infobase',
            'mailorder',
            'areacode',
            'telephone',
            'trckngcd',
            'addrsstyp',
            'yearbuilt',
            'age',
            'phnnspc',
            'dtflld',
            'crrt',
            'dlvrypnt',
            'lot',
            'footnote',
            'rtrncd',
            'anklnkrtrn',
            'cmtchflg',
            'cmvdt',
            'cmvtyp',
            'lcslnkrtrn',
            'nclnkrtrnc',
            'stlnkrtrnc',
            'dpvcmra',
            'dpvcnfrmtn',
            'dpvnstt',
            'dpvvacant',
            'prefix',
            'firstname',
            'lastname',
            'middle',
            'latitude',
            'longitude',
            'pkgmldt1',
            'pkgmldt2',
            'pkgmldt3',
            'pkgmldt4',
            'pkgmldt5',
            'pkgmldt6',
            'pkgmldt7',
            'pkgmldt8',
            'pkgmldt9',
            'pkgmldt10',
            'pkgmldt11',
            'pkgmldt12',
            'wlksqnc',
            'bsnssrsdnt',
            'chckdgt',
            'cdsfprcssn',
            'phone1',
            'invcdt',
            'invcnmbr',
            'rvntyp',
            'slamnt',
            'salesrep',
            'clbmmbr',
            'pkgmldt13',
            'pkgmldt15',
            'pkgmldt14',
            'purge',
            'pkgmldta1',
            'pkgmldta2',
            'pkgmldta3',
            'pkgmldta4',
            'pkgmldta5',
            'pkgmldta6',
            'pkgmldta7',
            'pkgmldta8',
            'pkgmldta9',
            'pkgmldta10',
            'pkgmldta11',
            'pkgmldta12',
            'lifetran',
            'lifeval',
            'pkgmldtb1',
            'pkgmldtb2',
            'pkgmldtb3',
            'pkgmldtb4',
            'pkgmldtb5',
            'pkgmldtb6',
            'pkgmldtb7',
            'pkgmldtb8',
            'pkgmldtb9',
            'pkgmldtb10',
            'pkgmldtb11',
            'pkgmldtb12',
            'company',
            'deleted',
        ];
    }
}
