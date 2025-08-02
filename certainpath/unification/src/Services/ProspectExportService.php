<?php

namespace App\Services;

use App\Entity\Prospect;

readonly class ProspectExportService
{
    public function getHeaders(): array
    {
        return [
            'Job Number',
            'Ring To',
            'Version Code',
            'CSR Full Name',
            'Intacct ID',
            'Prospect Number',
            'Recipient Name',
            'Recipient Address 1',
            'Recipient Address 2',
            'Recipient City',
            'Recipient State',
            'Recipient Zip Short',
            'Recipient Zip Long',
        ];
    }

    public function prepareRow(Prospect $prospect, array $metadata): array
    {
        $addressRecord = $prospect->getPreferredAddress();

        return [
            $metadata['Job Number'],
            $metadata['Ring To'],
            $metadata['Version Code'],
            $metadata['CSR Full Name'],
            $prospect->getCompany()?->getIdentifier(),
            $prospect->getId(),
            $prospect->getFullName() ?? ($prospect->getFirstName() . ' ' . $prospect->getLastName()),
            $addressRecord ? $addressRecord->getAddress1() : $prospect->getAddress1(),
            $addressRecord ? $addressRecord->getAddress2() : $prospect->getAddress2(),
            $addressRecord ? $addressRecord->getCity() : $prospect->getCity(),
            $addressRecord ? $addressRecord->getStateCode() : $prospect->getState(),
            $addressRecord ? $addressRecord->getPostalCodeShort() : $prospect->getPostalCodeShort(),
            $addressRecord ? $addressRecord->getPostalCode() : $prospect->getPostalCode(),
        ];
    }
}
