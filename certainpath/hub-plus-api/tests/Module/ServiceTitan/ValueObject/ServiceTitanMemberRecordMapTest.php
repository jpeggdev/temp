<?php

declare(strict_types=1);

namespace App\Tests\Module\ServiceTitan\ValueObject;

use App\Module\ServiceTitan\ValueObject\ServiceTitanMemberRecordMap;
use App\Tests\AbstractKernelTestCase;
use App\ValueObject\MemberRecord;

class ServiceTitanMemberRecordMapTest extends AbstractKernelTestCase
{
    public function testBasicMemberMappingWithCompleteData(): void
    {
        // Arrange
        $serviceTitanData = [
            'id' => 'CUST-12345',
            'firstName' => 'Jane',
            'lastName' => 'Smith',
            'name' => 'Jane Smith',
            'phoneNumber' => '555-123-4567',
            'email' => 'jane.smith@email.com',
            'active' => true,
            'status' => 'Active',
            'type' => 'Residential',
            'phoneNumbers' => [
                ['number' => '555-123-4567'],
                ['number' => '555-987-6543']
            ],
            'address' => [
                'street' => '123 Main St',
                'unit' => 'Apt 2',
                'city' => 'Anytown',
                'state' => 'CA',
                'zip' => '12345',
                'country' => 'US'
            ]
        ];

        // Act
        $record = ServiceTitanMemberRecordMap::fromServiceTitanData($serviceTitanData);

        // Assert - Core member fields
        self::assertInstanceOf(MemberRecord::class, $record);
        self::assertSame('CUST-12345', $record->customer_id);
        self::assertSame('Jane Smith', $record->customer_name);
        self::assertSame('Jane', $record->customer_first_name);
        self::assertSame('Smith', $record->customer_last_name);

        // Assert - Contact information
        self::assertSame('(555) 123-4567', $record->customer_phone_number_primary);
        self::assertSame('(555) 123-4567, (555) 987-6543', $record->customer_phone_numbers);

        // Assert - Address information
        self::assertSame('123 Main St', $record->street);
        self::assertSame('Apt 2', $record->unit);
        self::assertSame('Anytown', $record->city);
        self::assertSame('CA', $record->state);
        self::assertSame('12345', $record->zip);
        self::assertSame('US', $record->country);

        // Assert - Member-specific fields
        self::assertSame('Yes', $record->active_member);
        self::assertSame('Residential', $record->membership_type);
        self::assertSame('Active', $record->current_status);

        // Assert - Metadata
        self::assertSame('servicetitan_CUST-12345', $record->hub_plus_import_id);
        self::assertSame('1.0', $record->version);
    }

    public function testMemberMappingWithMinimalData(): void
    {
        // Arrange
        $serviceTitanData = [
            'id' => 'CUST-MIN',
            'firstName' => 'John'
        ];

        // Act
        $record = ServiceTitanMemberRecordMap::fromServiceTitanData($serviceTitanData);

        // Assert
        self::assertSame('CUST-MIN', $record->customer_id);
        self::assertSame('John', $record->customer_name);
        self::assertSame('John', $record->customer_first_name);
        self::assertSame('servicetitan_CUST-MIN', $record->hub_plus_import_id);
        self::assertSame('1.0', $record->version);

        // Assert defaults and null values
        self::assertNull($record->customer_last_name);
        self::assertNull($record->street);
        self::assertSame('US', $record->country); // Default value
        self::assertSame('Yes', $record->active_member); // Default to active
        self::assertSame('Customer', $record->membership_type); // Default type
        self::assertSame('Active', $record->current_status); // Default status
    }

    public function testCustomerNameExtractionFromDifferentSources(): void
    {
        // Test full name from 'name' field
        $serviceTitanData = [
            'id' => 'TEST1',
            'name' => 'Bob Johnson'
        ];
        $record = ServiceTitanMemberRecordMap::fromServiceTitanData($serviceTitanData);
        self::assertSame('Bob Johnson', $record->customer_name);

        // Test built from firstName and lastName
        $serviceTitanData = [
            'id' => 'TEST2',
            'firstName' => 'Alice',
            'lastName' => 'Wilson'
        ];
        $record = ServiceTitanMemberRecordMap::fromServiceTitanData($serviceTitanData);
        self::assertSame('Alice Wilson', $record->customer_name);

        // Test with only firstName
        $serviceTitanData = [
            'id' => 'TEST3',
            'firstName' => 'Charlie'
        ];
        $record = ServiceTitanMemberRecordMap::fromServiceTitanData($serviceTitanData);
        self::assertSame('Charlie', $record->customer_name);

        // Test with only lastName
        $serviceTitanData = [
            'id' => 'TEST4',
            'lastName' => 'Brown'
        ];
        $record = ServiceTitanMemberRecordMap::fromServiceTitanData($serviceTitanData);
        self::assertSame('Brown', $record->customer_name);

        // Test with no name data
        $serviceTitanData = ['id' => 'TEST5'];
        $record = ServiceTitanMemberRecordMap::fromServiceTitanData($serviceTitanData);
        self::assertNull($record->customer_name);
    }

    public function testPhoneNumberFormatting(): void
    {
        // Arrange
        $testCases = [
            '5551234567' => '(555) 123-4567',
            '555-123-4567' => '(555) 123-4567',
            '(555) 123-4567' => '(555) 123-4567',
            '555.123.4567' => '(555) 123-4567',
            '+15551234567' => '+15551234567', // 11 digits, keep original
            '123456' => '123456', // Too short, keep original
            7735551234 => '(773) 555-1234', // Integer input
            '' => null
        ];

        foreach ($testCases as $input => $expected) {
            $serviceTitanData = [
                'id' => 'TEST',
                'phoneNumber' => $input
            ];

            // Act
            $record = ServiceTitanMemberRecordMap::fromServiceTitanData($serviceTitanData);

            // Assert
            self::assertEquals($expected, $record->customer_phone_number_primary, "Failed for input: ".json_encode($input));
        }
    }

    public function testNullPhoneNumberHandling(): void
    {
        // Arrange
        $serviceTitanData = [
            'id' => 'TEST',
            'phoneNumber' => null,
            'phoneNumbers' => []
        ];

        // Act
        $record = ServiceTitanMemberRecordMap::fromServiceTitanData($serviceTitanData);

        // Assert
        self::assertNull($record->customer_phone_number_primary);
    }

    public function testMultiplePhoneNumbersHandling(): void
    {
        // Arrange
        $serviceTitanData = [
            'id' => 'TEST',
            'phoneNumber' => '555-123-4567',
            'phoneNumbers' => [
                ['number' => '555-123-4567'], // Duplicate, should be filtered
                ['number' => '555-987-6543'],
                ['number' => '555-111-2222']
            ]
        ];

        // Act
        $record = ServiceTitanMemberRecordMap::fromServiceTitanData($serviceTitanData);

        // Assert
        self::assertSame('(555) 123-4567', $record->customer_phone_number_primary);
        self::assertSame('(555) 123-4567, (555) 987-6543, (555) 111-2222', $record->customer_phone_numbers);
    }

    public function testPhoneNumbersFromArrayOnly(): void
    {
        // Arrange - No primary phone, only phoneNumbers array
        $serviceTitanData = [
            'id' => 'TEST',
            'phoneNumbers' => [
                ['number' => '555-111-2222'],
                ['number' => '555-333-4444']
            ]
        ];

        // Act
        $record = ServiceTitanMemberRecordMap::fromServiceTitanData($serviceTitanData);

        // Assert
        self::assertSame('(555) 111-2222', $record->customer_phone_number_primary);
        self::assertSame('(555) 111-2222, (555) 333-4444', $record->customer_phone_numbers);
    }

    public function testAddressExtractionFromPrimaryAddress(): void
    {
        // Arrange
        $serviceTitanData = [
            'id' => 'TEST',
            'address' => [
                'street' => '456 Oak Ave',
                'unit' => 'Suite 100',
                'city' => 'Springfield',
                'state' => 'IL',
                'zip' => '62701',
                'country' => 'US'
            ]
        ];

        // Act
        $record = ServiceTitanMemberRecordMap::fromServiceTitanData($serviceTitanData);

        // Assert
        self::assertSame('456 Oak Ave', $record->street);
        self::assertSame('Suite 100', $record->unit);
        self::assertSame('Springfield', $record->city);
        self::assertSame('IL', $record->state);
        self::assertSame('62701', $record->zip);
        self::assertSame('US', $record->country);
    }

    public function testAddressExtractionFromAddressesArray(): void
    {
        // Arrange - No primary address, use addresses array
        $serviceTitanData = [
            'id' => 'TEST',
            'addresses' => [
                [
                    'street' => '789 Pine Rd',
                    'city' => 'Hometown',
                    'state' => 'TX',
                    'zip' => '75001'
                ]
            ]
        ];

        // Act
        $record = ServiceTitanMemberRecordMap::fromServiceTitanData($serviceTitanData);

        // Assert
        self::assertSame('789 Pine Rd', $record->street);
        self::assertSame('Hometown', $record->city);
        self::assertSame('TX', $record->state);
        self::assertSame('75001', $record->zip);
        self::assertSame('US', $record->country); // Default
    }

    public function testAddressExtractionWithMissingData(): void
    {
        // Arrange
        $serviceTitanData = [
            'id' => 'TEST'
            // No address data
        ];

        // Act
        $record = ServiceTitanMemberRecordMap::fromServiceTitanData($serviceTitanData);

        // Assert
        self::assertNull($record->street);
        self::assertNull($record->unit);
        self::assertNull($record->city);
        self::assertNull($record->state);
        self::assertNull($record->zip);
        self::assertSame('US', $record->country); // Default value
    }

    public function testActiveStatusDetermination(): void
    {
        // Test explicit active flag
        $serviceTitanData = ['id' => 'TEST1', 'active' => true];
        $record = ServiceTitanMemberRecordMap::fromServiceTitanData($serviceTitanData);
        self::assertSame('Yes', $record->active_member);

        $serviceTitanData = ['id' => 'TEST2', 'active' => false];
        $record = ServiceTitanMemberRecordMap::fromServiceTitanData($serviceTitanData);
        self::assertSame('No', $record->active_member);

        // Test deactivated flag
        $serviceTitanData = ['id' => 'TEST3', 'deactivated' => true];
        $record = ServiceTitanMemberRecordMap::fromServiceTitanData($serviceTitanData);
        self::assertSame('No', $record->active_member);

        // Test status-based determination
        $serviceTitanData = ['id' => 'TEST4', 'status' => 'Active'];
        $record = ServiceTitanMemberRecordMap::fromServiceTitanData($serviceTitanData);
        self::assertSame('Yes', $record->active_member);

        $serviceTitanData = ['id' => 'TEST5', 'status' => 'Inactive'];
        $record = ServiceTitanMemberRecordMap::fromServiceTitanData($serviceTitanData);
        self::assertSame('No', $record->active_member);

        // Test default (no data)
        $serviceTitanData = ['id' => 'TEST6'];
        $record = ServiceTitanMemberRecordMap::fromServiceTitanData($serviceTitanData);
        self::assertSame('Yes', $record->active_member); // Default to active
    }

    public function testMembershipTypeExtraction(): void
    {
        // Test type field
        $serviceTitanData = ['id' => 'TEST1', 'type' => 'Commercial'];
        $record = ServiceTitanMemberRecordMap::fromServiceTitanData($serviceTitanData);
        self::assertSame('Commercial', $record->membership_type);

        // Test customerType field
        $serviceTitanData = ['id' => 'TEST2', 'customerType' => 'Premium'];
        $record = ServiceTitanMemberRecordMap::fromServiceTitanData($serviceTitanData);
        self::assertSame('Premium', $record->membership_type);

        // Test membership programs
        $serviceTitanData = [
            'id' => 'TEST3',
            'memberships' => [
                ['type' => 'Gold Member']
            ]
        ];
        $record = ServiceTitanMemberRecordMap::fromServiceTitanData($serviceTitanData);
        self::assertSame('Gold Member', $record->membership_type);

        // Test default
        $serviceTitanData = ['id' => 'TEST4'];
        $record = ServiceTitanMemberRecordMap::fromServiceTitanData($serviceTitanData);
        self::assertSame('Customer', $record->membership_type);
    }

    public function testCurrentStatusExtraction(): void
    {
        // Test explicit status field
        $serviceTitanData = ['id' => 'TEST1', 'status' => 'active'];
        $record = ServiceTitanMemberRecordMap::fromServiceTitanData($serviceTitanData);
        self::assertSame('Active', $record->current_status);

        // Test deactivated flag
        $serviceTitanData = ['id' => 'TEST2', 'deactivated' => true];
        $record = ServiceTitanMemberRecordMap::fromServiceTitanData($serviceTitanData);
        self::assertSame('Inactive', $record->current_status);

        // Test active flag
        $serviceTitanData = ['id' => 'TEST3', 'active' => false];
        $record = ServiceTitanMemberRecordMap::fromServiceTitanData($serviceTitanData);
        self::assertSame('Inactive', $record->current_status);

        // Test default
        $serviceTitanData = ['id' => 'TEST4'];
        $record = ServiceTitanMemberRecordMap::fromServiceTitanData($serviceTitanData);
        self::assertSame('Active', $record->current_status);
    }

    public function testStatusNormalization(): void
    {
        // Test various status formats
        $testCases = [
            'active' => 'Active',
            'ACTIVE' => 'Active',
            'current' => 'Active',
            'enabled' => 'Active',
            'good' => 'Active',
            'inactive' => 'Inactive',
            'INACTIVE' => 'Inactive',
            'disabled' => 'Inactive',
            'deactivated' => 'Inactive',
            'suspended' => 'Inactive',
            'pending' => 'Pending',
            'new' => 'Pending',
            'custom_status' => 'Custom_status',
        ];

        foreach ($testCases as $input => $expected) {
            $serviceTitanData = ['id' => 'TEST', 'status' => $input];
            $record = ServiceTitanMemberRecordMap::fromServiceTitanData($serviceTitanData);
            self::assertSame($expected, $record->current_status, "Failed for status: $input");
        }
    }

    public function testEmptyDataHandling(): void
    {
        // Arrange - Test with completely empty data
        $serviceTitanData = [];

        // Act
        $record = ServiceTitanMemberRecordMap::fromServiceTitanData($serviceTitanData);

        // Assert - Should handle gracefully without errors
        self::assertSame('', $record->customer_id); // Empty string for empty id
        self::assertNull($record->customer_name);
        self::assertNull($record->customer_first_name);
        self::assertNull($record->customer_last_name);
        self::assertSame('servicetitan_unknown', $record->hub_plus_import_id);
        self::assertSame('1.0', $record->version);

        // Defaults
        self::assertSame('US', $record->country);
        self::assertSame('Yes', $record->active_member);
        self::assertSame('Customer', $record->membership_type);
        self::assertSame('Active', $record->current_status);
    }

    public function testNullValueHandling(): void
    {
        // Arrange - Test with explicit null values
        $serviceTitanData = [
            'id' => null,
            'firstName' => null,
            'lastName' => null,
            'phoneNumber' => null,
            'address' => null,
            'status' => null,
            'type' => null
        ];

        // Act
        $record = ServiceTitanMemberRecordMap::fromServiceTitanData($serviceTitanData);

        // Assert - Should handle null values gracefully
        self::assertSame('', $record->customer_id); // Empty string for null id
        self::assertNull($record->customer_first_name);
        self::assertNull($record->customer_last_name);
        self::assertNull($record->customer_name);
        self::assertNull($record->customer_phone_number_primary);
        self::assertNull($record->street);
        self::assertSame('servicetitan_unknown', $record->hub_plus_import_id);
    }

    /**
     * Test mapping accuracy against a realistic ServiceTitan API response
     */
    public function testMappingAccuracyWithRealisticData(): void
    {
        // Arrange - Realistic ServiceTitan customer data structure
        $serviceTitanData = [
            'id' => 12345678,
            'firstName' => 'Michael',
            'lastName' => 'Johnson',
            'name' => 'Michael Johnson',
            'email' => 'michael.johnson@email.com',
            'phoneNumber' => '7735551234',
            'active' => true,
            'status' => 'Active',
            'type' => 'Residential',
            'customerType' => 'Premium',
            'createdOn' => '2024-01-15T09:00:00.000Z',
            'modifiedOn' => '2025-07-29T14:30:00.000Z',
            'phoneNumbers' => [
                [
                    'number' => '7735551234',
                    'type' => 'Mobile'
                ],
                [
                    'number' => '8475559876',
                    'type' => 'Home'
                ]
            ],
            'addresses' => [
                [
                    'street' => '1234 Chicago Avenue',
                    'unit' => 'Unit 3B',
                    'city' => 'Chicago',
                    'state' => 'IL',
                    'zip' => '60622',
                    'country' => 'US',
                    'type' => 'Service'
                ]
            ],
            'address' => [
                'street' => '1234 Chicago Avenue',
                'unit' => 'Unit 3B',
                'city' => 'Chicago',
                'state' => 'IL',
                'zip' => '60622',
                'country' => 'US'
            ],
            'memberships' => [
                [
                    'id' => 9001,
                    'type' => 'HVAC Maintenance Plan',
                    'active' => true,
                    'startDate' => '2024-01-15',
                    'renewalDate' => '2025-01-15'
                ]
            ]
        ];

        // Act
        $record = ServiceTitanMemberRecordMap::fromServiceTitanData($serviceTitanData);

        // Assert - Verify all critical mappings
        self::assertSame('12345678', $record->customer_id);
        self::assertSame('Michael Johnson', $record->customer_name);
        self::assertSame('Michael', $record->customer_first_name);
        self::assertSame('Johnson', $record->customer_last_name);

        // Contact information
        self::assertSame('(773) 555-1234', $record->customer_phone_number_primary);
        self::assertSame('(773) 555-1234, (847) 555-9876', $record->customer_phone_numbers);

        // Address
        self::assertSame('1234 Chicago Avenue', $record->street);
        self::assertSame('Unit 3B', $record->unit);
        self::assertSame('Chicago', $record->city);
        self::assertSame('IL', $record->state);
        self::assertSame('60622', $record->zip);
        self::assertSame('US', $record->country);

        // Member status and type
        self::assertSame('Yes', $record->active_member);
        self::assertSame('Residential', $record->membership_type); // type takes precedence over customerType
        self::assertSame('Active', $record->current_status);

        // Metadata
        self::assertSame('servicetitan_12345678', $record->hub_plus_import_id);
        self::assertSame('1.0', $record->version);
    }

    public function testMembershipTypePreferencePriority(): void
    {
        // Test priority: type > customerType > memberships > default

        // Test 1: Only type field
        $serviceTitanData = [
            'id' => 'TEST1',
            'type' => 'Commercial',
            'customerType' => 'Premium',
            'memberships' => [['type' => 'Gold']]
        ];
        $record = ServiceTitanMemberRecordMap::fromServiceTitanData($serviceTitanData);
        self::assertSame('Commercial', $record->membership_type);

        // Test 2: Only customerType field
        $serviceTitanData = [
            'id' => 'TEST2',
            'customerType' => 'Premium',
            'memberships' => [['type' => 'Gold']]
        ];
        $record = ServiceTitanMemberRecordMap::fromServiceTitanData($serviceTitanData);
        self::assertSame('Premium', $record->membership_type);

        // Test 3: Only memberships
        $serviceTitanData = [
            'id' => 'TEST3',
            'memberships' => [['type' => 'Gold']]
        ];
        $record = ServiceTitanMemberRecordMap::fromServiceTitanData($serviceTitanData);
        self::assertSame('Gold', $record->membership_type);

        // Test 4: Default
        $serviceTitanData = ['id' => 'TEST4'];
        $record = ServiceTitanMemberRecordMap::fromServiceTitanData($serviceTitanData);
        self::assertSame('Customer', $record->membership_type);
    }

    public function testAddressSourcePreferencePriority(): void
    {
        // Test priority: address > addresses[0]

        // Test 1: Both address and addresses present
        $serviceTitanData = [
            'id' => 'TEST1',
            'address' => [
                'street' => 'Primary Street',
                'city' => 'Primary City'
            ],
            'addresses' => [
                [
                    'street' => 'Secondary Street',
                    'city' => 'Secondary City'
                ]
            ]
        ];
        $record = ServiceTitanMemberRecordMap::fromServiceTitanData($serviceTitanData);
        self::assertSame('Primary Street', $record->street);
        self::assertSame('Primary City', $record->city);

        // Test 2: Only addresses array
        $serviceTitanData = [
            'id' => 'TEST2',
            'addresses' => [
                [
                    'street' => 'Array Street',
                    'city' => 'Array City'
                ]
            ]
        ];
        $record = ServiceTitanMemberRecordMap::fromServiceTitanData($serviceTitanData);
        self::assertSame('Array Street', $record->street);
        self::assertSame('Array City', $record->city);
    }
}
