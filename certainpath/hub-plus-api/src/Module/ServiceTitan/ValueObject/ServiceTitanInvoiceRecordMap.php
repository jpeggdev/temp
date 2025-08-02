<?php

declare(strict_types=1);

namespace App\Module\ServiceTitan\ValueObject;

use App\ValueObject\InvoiceRecord;

/**
 * ServiceTitan-specific transformer that converts ServiceTitan API invoice responses
 * to InvoiceRecord instances used throughout the Hub Plus API
 */
class ServiceTitanInvoiceRecordMap
{
    /**
     * Transform ServiceTitan API invoice data to InvoiceRecord instance
     *
     * @param array<string, mixed> $serviceTitanInvoiceData
     */
    public static function fromServiceTitanData(array $serviceTitanInvoiceData): InvoiceRecord
    {
        $record = InvoiceRecord::getRecordInstance();

        // Transform and map all ServiceTitan data to record properties
        $transformer = new self();
        $transformer->populateRecord($record, $serviceTitanInvoiceData);

        return $record;
    }

    /**
     * Populate InvoiceRecord with transformed ServiceTitan data
     *
     * @param array<string, mixed> $serviceTitanData
     */
    private function populateRecord(InvoiceRecord $record, array $serviceTitanData): void
    {
        // Core invoice identification
        $record->invoice_number = $serviceTitanData['number'] ?? null;
        $record->job_number = $serviceTitanData['jobNumber'] ?? null;
        $record->total = $this->parseDecimal($serviceTitanData['total'] ?? null);
        $record->first_appointment = $this->parseDate($serviceTitanData['invoiceDate'] ?? null);

        // Job and service information
        $record->job_type = $serviceTitanData['jobType']['name'] ?? null;
        $record->summary = $serviceTitanData['summary'] ?? null;
        $record->invoice_summary = $serviceTitanData['summary'] ?? null;
        $record->zone = $serviceTitanData['businessUnit']['name'] ?? null;

        // Customer information from nested structure
        $this->populateCustomerData($record, $serviceTitanData);

        // Address information
        $this->populateAddressData($record, $serviceTitanData);

        // Metadata for tracking
        $record->hub_plus_import_id = 'servicetitan_'.($serviceTitanData['id'] ?? 'unknown');
    }

    /**
     * Populate customer-specific fields
     *
     * @param array<string, mixed> $serviceTitanData
     */
    private function populateCustomerData(InvoiceRecord $record, array $serviceTitanData): void
    {
        $customer = $serviceTitanData['customer'] ?? [];

        // Customer identification
        $record->customer_id = (string) ($serviceTitanData['customerId'] ?? '');

        // Customer name handling
        if (!empty($customer['name'])) {
            $record->customer_name = $customer['name'];
        } elseif (!empty($customer['firstName']) || !empty($customer['lastName'])) {
            $firstName = $customer['firstName'] ?? '';
            $lastName = $customer['lastName'] ?? '';
            $record->customer_name = trim($firstName.' '.$lastName);
        }

        $record->customer_first_name = $customer['firstName'] ?? null;
        $record->customer_last_name = $customer['lastName'] ?? null;

        // Phone number handling
        $record->customer_phone_number_primary = $this->extractPrimaryPhone($customer);
        $record->customer_phone_numbers = $this->extractAllPhones($customer);
    }

    /**
     * Populate address-specific fields
     *
     * @param array<string, mixed> $serviceTitanData
     */
    private function populateAddressData(InvoiceRecord $record, array $serviceTitanData): void
    {
        $location = $serviceTitanData['location'] ?? [];
        $address = $location['address'] ?? [];

        $record->street = $address['street'] ?? null;
        $record->unit = $address['unit'] ?? null;
        $record->city = $address['city'] ?? null;
        $record->state = $address['state'] ?? null;
        $record->zip = $address['zip'] ?? null;
        $record->country = $address['country'] ?? 'US';
    }

    /**
     * Parse ServiceTitan date string to standardized format
     */
    private function parseDate(mixed $dateValue): ?string
    {
        if (!$dateValue) {
            return null;
        }

        try {
            $date = new \DateTime((string) $dateValue);
            return $date->format('Y-m-d H:i:s');
        } catch (\Exception) {
            return null;
        }
    }

    /**
     * Parse ServiceTitan decimal/currency values
     */
    private function parseDecimal(mixed $amount): ?string
    {
        if ($amount === null || $amount === '') {
            return null;
        }

        // Handle numeric values directly
        if (is_numeric($amount)) {
            return (string) (float) $amount;
        }

        // Handle string values with currency formatting
        if (is_string($amount)) {
            $cleanAmount = preg_replace('/[^\d.-]/', '', $amount);

            if (!is_numeric($cleanAmount)) {
                return null;
            }

            return (string) (float) $cleanAmount;
        }

        return null;
    }

    /**
     * Extract primary phone number from ServiceTitan customer data
     *
     * @param array<string, mixed> $customerData
     */
    private function extractPrimaryPhone(array $customerData): ?string
    {
        // Try primary phone number first
        if (!empty($customerData['phoneNumber'])) {
            return $this->formatPhoneNumber($customerData['phoneNumber']);
        }

        // Try phone numbers array
        $phoneNumbers = $customerData['phoneNumbers'] ?? [];
        if (!empty($phoneNumbers) && is_array($phoneNumbers)) {
            $primaryPhone = $phoneNumbers[0]['number'] ?? null;
            if ($primaryPhone) {
                return $this->formatPhoneNumber($primaryPhone);
            }
        }

        return null;
    }

    /**
     * Extract all phone numbers from ServiceTitan customer data
     *
     * @param array<string, mixed> $customerData
     */
    private function extractAllPhones(array $customerData): ?string
    {
        $phoneNumbers = [];

        // Add primary phone if available
        if (!empty($customerData['phoneNumber'])) {
            $phoneNumbers[] = $this->formatPhoneNumber($customerData['phoneNumber']);
        }

        // Add additional phones from array
        $additionalPhones = $customerData['phoneNumbers'] ?? [];
        if (is_array($additionalPhones)) {
            foreach ($additionalPhones as $phone) {
                if (!empty($phone['number'])) {
                    $formatted = $this->formatPhoneNumber($phone['number']);
                    if ($formatted && !in_array($formatted, $phoneNumbers, true)) {
                        $phoneNumbers[] = $formatted;
                    }
                }
            }
        }

        return !empty($phoneNumbers) ? implode(', ', $phoneNumbers) : null;
    }

    /**
     * Format phone number to standardized format
     */
    private function formatPhoneNumber(mixed $phoneNumber): ?string
    {
        if (!$phoneNumber) {
            return null;
        }

        $phoneString = (string) $phoneNumber;

        // Remove all non-digit characters
        $digits = preg_replace('/\D/', '', $phoneString);

        if (!$digits) {
            return null;
        }

        // Format as (XXX) XXX-XXXX for 10-digit US numbers
        if (strlen($digits) === 10) {
            return sprintf(
                '(%s) %s-%s',
                substr($digits, 0, 3),
                substr($digits, 3, 3),
                substr($digits, 6, 4)
            );
        }

        // Return original for other formats
        return $phoneString;
    }
}
