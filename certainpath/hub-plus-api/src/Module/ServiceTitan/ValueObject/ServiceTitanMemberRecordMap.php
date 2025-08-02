<?php

declare(strict_types=1);

namespace App\Module\ServiceTitan\ValueObject;

use App\ValueObject\MemberRecord;

/**
 * ServiceTitan-specific transformer that converts ServiceTitan API customer responses
 * to MemberRecord instances used throughout the Hub Plus API
 */
class ServiceTitanMemberRecordMap
{
    /**
     * Transform ServiceTitan API customer data to MemberRecord instance
     *
     * @param array<string, mixed> $serviceTitanCustomerData
     */
    public static function fromServiceTitanData(array $serviceTitanCustomerData): MemberRecord
    {
        /** @var MemberRecord $record */
        $record = MemberRecord::getRecordInstance();

        // Transform and map all ServiceTitan data to record properties
        $transformer = new self();
        $transformer->populateRecord($record, $serviceTitanCustomerData);

        return $record;
    }

    /**
     * Populate MemberRecord with transformed ServiceTitan data
     *
     * @param array<string, mixed> $serviceTitanData
     */
    private function populateRecord(MemberRecord $record, array $serviceTitanData): void
    {
        // Core member identification
        $record->customer_id = (string) ($serviceTitanData['id'] ?? '');

        // Customer name handling
        if (!empty($serviceTitanData['name'])) {
            $record->customer_name = $serviceTitanData['name'];
        } elseif (!empty($serviceTitanData['firstName']) || !empty($serviceTitanData['lastName'])) {
            $firstName = $serviceTitanData['firstName'] ?? '';
            $lastName = $serviceTitanData['lastName'] ?? '';
            $record->customer_name = trim($firstName.' '.$lastName);
        }

        $record->customer_first_name = $serviceTitanData['firstName'] ?? null;
        $record->customer_last_name = $serviceTitanData['lastName'] ?? null;

        // Contact information
        $record->customer_phone_number_primary = $this->extractPrimaryPhone($serviceTitanData);
        $record->customer_phone_numbers = $this->extractAllPhones($serviceTitanData);

        // Address information
        $this->populateAddressData($record, $serviceTitanData);

        // Member-specific fields
        $record->active_member = $this->determineActiveStatus($serviceTitanData);
        $record->membership_type = $this->extractMembershipType($serviceTitanData);
        $record->current_status = $this->extractCurrentStatus($serviceTitanData);

        // Metadata for tracking
        $record->hub_plus_import_id = 'servicetitan_'.($serviceTitanData['id'] ?? 'unknown');
        $record->version = '1.0'; // ServiceTitan import version
    }

    /**
     * Populate address-specific fields
     *
     * @param array<string, mixed> $serviceTitanData
     */
    private function populateAddressData(MemberRecord $record, array $serviceTitanData): void
    {
        // Try primary address first
        $address = $serviceTitanData['address'] ?? [];
        if (!empty($address)) {
            $record->street = $address['street'] ?? null;
            $record->unit = $address['unit'] ?? null;
            $record->city = $address['city'] ?? null;
            $record->state = $address['state'] ?? null;
            $record->zip = $address['zip'] ?? null;
            $record->country = $address['country'] ?? 'US';
            return;
        }

        // Try addresses array
        $addresses = $serviceTitanData['addresses'] ?? [];
        if (!empty($addresses) && is_array($addresses)) {
            $primaryAddress = $addresses[0] ?? [];
            $record->street = $primaryAddress['street'] ?? null;
            $record->unit = $primaryAddress['unit'] ?? null;
            $record->city = $primaryAddress['city'] ?? null;
            $record->state = $primaryAddress['state'] ?? null;
            $record->zip = $primaryAddress['zip'] ?? null;
            $record->country = $primaryAddress['country'] ?? 'US';
            return;
        }

        // Default values for missing address
        $record->country = 'US';
    }

    /**
     * Extract primary phone number from ServiceTitan customer data
     *
     * @param array<string, mixed> $serviceTitanData
     */
    private function extractPrimaryPhone(array $serviceTitanData): ?string
    {
        // Try primary phone number first
        if (!empty($serviceTitanData['phoneNumber'])) {
            return $this->formatPhoneNumber($serviceTitanData['phoneNumber']);
        }

        // Try phone numbers array
        $phoneNumbers = $serviceTitanData['phoneNumbers'] ?? [];
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
     * @param array<string, mixed> $serviceTitanData
     */
    private function extractAllPhones(array $serviceTitanData): ?string
    {
        $phoneNumbers = [];

        // Add primary phone if available
        if (!empty($serviceTitanData['phoneNumber'])) {
            $phoneNumbers[] = $this->formatPhoneNumber($serviceTitanData['phoneNumber']);
        }

        // Add additional phones from array
        $additionalPhones = $serviceTitanData['phoneNumbers'] ?? [];
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

    /**
     * Determine active member status from ServiceTitan customer data
     *
     * @param array<string, mixed> $serviceTitanData
     */
    private function determineActiveStatus(array $serviceTitanData): string
    {
        // Check if customer has been deactivated or marked inactive
        if (isset($serviceTitanData['deactivated']) && $serviceTitanData['deactivated']) {
            return 'No';
        }

        // Check status field
        $status = $serviceTitanData['status'] ?? null;
        if ($status) {
            return $this->mapStatusToActiveFlag($status);
        }

        // ServiceTitan customers are typically active if they have recent activity
        $isActive = $serviceTitanData['active'] ?? true;
        return $isActive ? 'Yes' : 'No';
    }

    /**
     * Extract membership type from ServiceTitan customer data
     *
     * @param array<string, mixed> $serviceTitanData
     */
    private function extractMembershipType(array $serviceTitanData): string
    {
        // Priority: type > customerType > memberships > default

        // ServiceTitan may have customer types or categories
        if (!empty($serviceTitanData['type'])) {
            return $serviceTitanData['type'];
        }

        if (!empty($serviceTitanData['customerType'])) {
            return $serviceTitanData['customerType'];
        }

        // Check for membership programs
        $memberships = $serviceTitanData['memberships'] ?? [];
        if (!empty($memberships) && is_array($memberships)) {
            $activeMembership = $memberships[0] ?? [];
            if (!empty($activeMembership['type'])) {
                return $activeMembership['type'];
            }
        }

        // Default to regular customer
        return 'Customer';
    }

    /**
     * Extract current status from ServiceTitan customer data
     *
     * @param array<string, mixed> $serviceTitanData
     */
    private function extractCurrentStatus(array $serviceTitanData): string
    {
        // Check for explicit status field
        if (!empty($serviceTitanData['status'])) {
            return $this->normalizeStatus($serviceTitanData['status']);
        }

        // Check if customer is deactivated
        if (isset($serviceTitanData['deactivated']) && $serviceTitanData['deactivated']) {
            return 'Inactive';
        }

        // Check active flag
        $isActive = $serviceTitanData['active'] ?? true;
        return $isActive ? 'Active' : 'Inactive';
    }

    /**
     * Map ServiceTitan status to active member flag
     */
    private function mapStatusToActiveFlag(string $status): string
    {
        $activeStatuses = ['active', 'current', 'enabled', 'good'];

        return in_array(strtolower($status), $activeStatuses, true) ? 'Yes' : 'No';
    }

    /**
     * Normalize status to standard format
     */
    private function normalizeStatus(string $status): string
    {
        $status = trim(strtolower($status));

        return match($status) {
            'active', 'current', 'enabled', 'good' => 'Active',
            'inactive', 'disabled', 'deactivated', 'suspended' => 'Inactive',
            'pending', 'new' => 'Pending',
            default => ucfirst($status)
        };
    }
}
