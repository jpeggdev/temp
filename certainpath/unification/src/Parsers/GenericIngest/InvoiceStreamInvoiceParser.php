<?php

namespace App\Parsers\GenericIngest;

use App\Parsers\Mixins\InvoiceMixin;
use App\ValueObjects\InvoiceObject;
use App\ValueObjects\ProspectObject;
use DateTimeImmutable;

class InvoiceStreamInvoiceParser extends GenericIngestParser
{
    use InvoiceMixin;

    public function parseRecord(array $record = [ ]): InvoiceObject
    {
        $fullName = $record['customername'] ?? null;
        $firstName = $record['customerfirstname'] ?? null;
        $lastName = $record['customerlastname'] ?? null;
        $tradeName = $record['trade'] ?? null;
        $street  = $record['street'] ?? null;
        $unit  = $record['unit'] ?? null;
        $city = $record['city'] ?? null;
        $state = $record['state'] ?? null;
        $postalCode = $record['zip'] ?? null ;
        $description = $record['description'] ?? $record['summary'] ?? 'Imported Invoice';
        $summary = $record['summary'] ?? null;
        $jobType = $record['jobtype'] ?? null;
        $zone = $record['zone'] ?? null;
        $total = number_format(
            $record['total'] ?? null,
            2,
            '.',
            ''
        );
        $invoicedAt =
            self::getInvoicedAtValue($record);

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
            '_extra' => $record,
        ]);
        $prospectObject->externalId = $this->getExternalId(
            $prospectObject->getKey()
        );
        $invoiceObject = new InvoiceObject([
            'tradeName' => $tradeName,
            'companyId' => $this->getCompanyId(),
            'prospect' => $prospectObject,
            'total' => $total,
            'description' => $description,
            'summary' => $summary,
            'zone' => $zone,
            'jobType' => $jobType,
            'invoiceNumber' => self::getInvoiceNumberFromRecord($record),
            'invoicedAt' => $invoicedAt,
            '_extra' => $record,
        ]);

        $invoiceObject->externalId = $this->getExternalId(
            $invoiceObject->getKey()
        );

        return $invoiceObject;
    }


    public static function getRequiredHeaders(): array
    {
        return [
            'customername',
            'customerfirstname',
            'customerlastname',
            'street',
            'unit',
            'city',
            'state',
            'zip',
            'invoicenumber',
            'summary',
            'total',
            'firstappointment',
        ];
    }

    /**
     * @param array|null $record
     * @return DateTimeImmutable|false|null
     */
    public static function getInvoicedAtValue(?array $record): null|false|DateTimeImmutable
    {
        if (empty($record['firstappointment'])) {
            return null;
        }

        if (is_numeric($record['firstappointment'])) {
            return (new DateTimeImmutable())->setTimestamp((int)$record['firstappointment']);
        }

        $date = date_create_immutable($record['firstappointment']);
        return $date ?: null;
    }
}
