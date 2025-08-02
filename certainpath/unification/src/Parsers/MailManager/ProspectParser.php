<?php

namespace App\Parsers\MailManager;

use App\Parsers\Mixins\EmailAddressMixin;
use App\ValueObjects\ProspectObject;

class ProspectParser extends MailManagerParser
{
    use EmailAddressMixin;

    public function parseRecord(array $record = [ ]): ProspectObject
    {
        $postalCode = (string) $record['zip4'];
        $prospectObject = new ProspectObject([
            'company' => $this->getCompanyIdentifier(),
            'companyId' => $this->getCompanyId(),
            'fullName' => $record['fullname'],
            'firstName' => $record['firstname'],
            'lastName' => $record['lastname'],
            'address1' => $record['dlvryaddrs'],
            'city' => $record['city'],
            'state' => $record['state'],
            'postalCode' => $postalCode,
            'isDeleted' => (bool)$record['deleted'],
            'doNotMail' => $this->shouldNotMail($record['purge'] ?? ''),
            '_extra' => $record,
        ]);
        $prospectObject->externalId = $this->getExternalId(
            $prospectObject->getKey()
        );

        return $prospectObject;
    }

    private function shouldNotMail(string $purgeValue): bool
    {
        // Map purge field values to do_not_mail status
        // Based on MailManager conventions, 'purge' typically indicates records that should not be mailed
        $doNotMailValues = ['purge', 'dnm', 'do not mail', 'remove', 'delete'];
        
        return in_array(strtolower(trim($purgeValue)), $doNotMailValues, true);
    }

    public static function getRequiredHeaders(): array
    {
        return [
            'city',
            'deleted',
            'dlvryaddrs',
            'firstname',
            'fullname',
            'lastname',
            'purge',
            'state',
            'zip4',
        ];
    }
}
