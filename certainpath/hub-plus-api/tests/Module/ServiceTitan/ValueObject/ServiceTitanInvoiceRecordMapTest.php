<?php

declare(strict_types=1);

namespace App\Tests\Module\ServiceTitan\ValueObject;

use App\Module\ServiceTitan\ValueObject\ServiceTitanInvoiceRecordMap;
use App\Tests\AbstractKernelTestCase;
use App\ValueObject\InvoiceRecord;

class ServiceTitanInvoiceRecordMapTest extends AbstractKernelTestCase
{
    public function testBasicInvoiceMappingWithCompleteData(): void
    {
        // Arrange
        $serviceTitanData = [
            'id' => 'ST-12345',
            'customerId' => 'CUST-67890',
            'number' => 'INV-001',
            'invoiceDate' => '2025-07-29T10:00:00Z',
            'total' => '$1,234.56',
            'jobNumber' => 'JOB-789',
            'summary' => 'HVAC repair service',
            'jobType' => [
                'name' => 'Service Call'
            ],
            'businessUnit' => [
                'name' => 'North Zone'
            ],
            'customer' => [
                'firstName' => 'John',
                'lastName' => 'Doe',
                'name' => 'John Doe',
                'phoneNumber' => '555-123-4567',
                'phoneNumbers' => [
                    ['number' => '555-123-4567'],
                    ['number' => '555-987-6543']
                ]
            ],
            'location' => [
                'address' => [
                    'street' => '123 Main St',
                    'unit' => 'Apt 2',
                    'city' => 'Anytown',
                    'state' => 'CA',
                    'zip' => '12345',
                    'country' => 'US'
                ]
            ]
        ];

        // Act
        $record = ServiceTitanInvoiceRecordMap::fromServiceTitanData($serviceTitanData);

        // Assert - Core invoice fields
        self::assertInstanceOf(InvoiceRecord::class, $record);
        self::assertSame('INV-001', $record->invoice_number);
        self::assertSame('CUST-67890', $record->customer_id);
        self::assertSame('1234.56', $record->total);
        self::assertSame('2025-07-29 10:00:00', $record->first_appointment);

        // Assert - Job information
        self::assertSame('JOB-789', $record->job_number);
        self::assertSame('Service Call', $record->job_type);
        self::assertSame('HVAC repair service', $record->summary);
        self::assertSame('HVAC repair service', $record->invoice_summary);
        self::assertSame('North Zone', $record->zone);

        // Assert - Customer information
        self::assertSame('John Doe', $record->customer_name);
        self::assertSame('John', $record->customer_first_name);
        self::assertSame('Doe', $record->customer_last_name);
        self::assertSame('(555) 123-4567', $record->customer_phone_number_primary);
        self::assertSame('(555) 123-4567, (555) 987-6543', $record->customer_phone_numbers);

        // Assert - Address information
        self::assertSame('123 Main St', $record->street);
        self::assertSame('Apt 2', $record->unit);
        self::assertSame('Anytown', $record->city);
        self::assertSame('CA', $record->state);
        self::assertSame('12345', $record->zip);
        self::assertSame('US', $record->country);

        // Assert - Metadata
        self::assertSame('servicetitan_ST-12345', $record->hub_plus_import_id);
    }

    public function testInvoiceMappingWithMinimalData(): void
    {
        // Arrange
        $serviceTitanData = [
            'id' => 'ST-MIN',
            'customerId' => 'CUST-MIN',
            'number' => 'INV-MIN',
            'total' => '100.00'
        ];

        // Act
        $record = ServiceTitanInvoiceRecordMap::fromServiceTitanData($serviceTitanData);

        // Assert
        self::assertSame('INV-MIN', $record->invoice_number);
        self::assertSame('CUST-MIN', $record->customer_id);
        self::assertSame('100', $record->total);
        self::assertSame('servicetitan_ST-MIN', $record->hub_plus_import_id);

        // Assert null values for missing data
        self::assertNull($record->first_appointment);
        self::assertNull($record->customer_name);
        self::assertNull($record->street);
    }

    public function testDateParsingWithValidDates(): void
    {
        // Arrange
        $testCases = [
            '2025-07-29T10:00:00Z' => '2025-07-29 10:00:00',
            '2025-12-31T23:59:59' => '2025-12-31 23:59:59',
            '2025-01-01T00:00:00+00:00' => '2025-01-01 00:00:00',
        ];

        foreach ($testCases as $input => $expected) {
            $serviceTitanData = [
                'id' => 'TEST',
                'invoiceDate' => $input
            ];

            // Act
            $record = ServiceTitanInvoiceRecordMap::fromServiceTitanData($serviceTitanData);

            // Assert
            self::assertSame($expected, $record->first_appointment, "Failed for input: $input");
        }
    }

    public function testDateParsingWithInvalidDates(): void
    {
        // Arrange
        $invalidDates = [
            'invalid-date',
            '2025-13-32', // Invalid month/day
            '',
            null
        ];

        foreach ($invalidDates as $invalidDate) {
            $serviceTitanData = [
                'id' => 'TEST',
                'invoiceDate' => $invalidDate
            ];

            // Act
            $record = ServiceTitanInvoiceRecordMap::fromServiceTitanData($serviceTitanData);

            // Assert
            self::assertNull($record->first_appointment, "Should be null for: ".json_encode($invalidDate));
        }
    }

    public function testDecimalParsingWithVariousFormats(): void
    {
        // Arrange
        $testCases = [
            '$1,234.56' => '1234.56',
            '1234.56' => '1234.56',
            '$10.00' => '10',
            '0.99' => '0.99',
            '1000' => '1000',
            '$2,500.75' => '2500.75',
        ];

        // Test numeric values separately to avoid array key conversion issues
        $numericTestCases = [
            ['input' => 1234.56, 'expected' => '1234.56'],
            ['input' => 485.75, 'expected' => '485.75'],
        ];

        foreach ($testCases as $input => $expected) {
            $serviceTitanData = [
                'id' => 'TEST',
                'total' => $input
            ];

            // Act
            $record = ServiceTitanInvoiceRecordMap::fromServiceTitanData($serviceTitanData);

            // Assert
            self::assertSame($expected, $record->total, "Failed for input: ".json_encode($input));
        }

        // Test numeric values
        foreach ($numericTestCases as $testCase) {
            $serviceTitanData = [
                'id' => 'TEST',
                'total' => $testCase['input']
            ];

            // Act
            $record = ServiceTitanInvoiceRecordMap::fromServiceTitanData($serviceTitanData);

            // Assert
            self::assertSame($testCase['expected'], $record->total, "Failed for numeric input: ".json_encode($testCase['input']));
        }
    }

    public function testDecimalParsingWithInvalidAmounts(): void
    {
        // Arrange
        $invalidAmounts = [
            'invalid-amount',
            'abc',
            '',
            null
        ];

        foreach ($invalidAmounts as $invalidAmount) {
            $serviceTitanData = [
                'id' => 'TEST',
                'total' => $invalidAmount
            ];

            // Act
            $record = ServiceTitanInvoiceRecordMap::fromServiceTitanData($serviceTitanData);

            // Assert
            self::assertNull($record->total, "Should be null for: ".json_encode($invalidAmount));
        }
    }

    public function testCustomerNameExtractionFromDifferentSources(): void
    {
        // Test full name from 'name' field
        $serviceTitanData = [
            'id' => 'TEST1',
            'customer' => [
                'name' => 'Jane Smith'
            ]
        ];
        $record = ServiceTitanInvoiceRecordMap::fromServiceTitanData($serviceTitanData);
        self::assertSame('Jane Smith', $record->customer_name);

        // Test built from firstName and lastName
        $serviceTitanData = [
            'id' => 'TEST2',
            'customer' => [
                'firstName' => 'Bob',
                'lastName' => 'Johnson'
            ]
        ];
        $record = ServiceTitanInvoiceRecordMap::fromServiceTitanData($serviceTitanData);
        self::assertSame('Bob Johnson', $record->customer_name);

        // Test with only firstName
        $serviceTitanData = [
            'id' => 'TEST3',
            'customer' => [
                'firstName' => 'Alice'
            ]
        ];
        $record = ServiceTitanInvoiceRecordMap::fromServiceTitanData($serviceTitanData);
        self::assertSame('Alice', $record->customer_name);

        // Test with no customer data
        $serviceTitanData = ['id' => 'TEST4'];
        $record = ServiceTitanInvoiceRecordMap::fromServiceTitanData($serviceTitanData);
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
            3125551234 => '(312) 555-1234', // Integer input
            '' => null
        ];

        foreach ($testCases as $input => $expected) {
            $serviceTitanData = [
                'id' => 'TEST',
                'customer' => [
                    'phoneNumber' => $input
                ]
            ];

            // Act
            $record = ServiceTitanInvoiceRecordMap::fromServiceTitanData($serviceTitanData);

            // Assert
            self::assertEquals($expected, $record->customer_phone_number_primary, "Failed for input: ".json_encode($input));
        }
    }

    public function testNullPhoneNumberHandling(): void
    {
        // Arrange
        $serviceTitanData = [
            'id' => 'TEST',
            'customer' => [
                'phoneNumber' => null,
                'phoneNumbers' => []
            ]
        ];

        // Act
        $record = ServiceTitanInvoiceRecordMap::fromServiceTitanData($serviceTitanData);

        // Assert
        self::assertNull($record->customer_phone_number_primary);
    }

    public function testMultiplePhoneNumbersHandling(): void
    {
        // Arrange
        $serviceTitanData = [
            'id' => 'TEST',
            'customer' => [
                'phoneNumber' => '555-123-4567',
                'phoneNumbers' => [
                    ['number' => '555-123-4567'], // Duplicate, should be filtered
                    ['number' => '555-987-6543'],
                    ['number' => '555-111-2222']
                ]
            ]
        ];

        // Act
        $record = ServiceTitanInvoiceRecordMap::fromServiceTitanData($serviceTitanData);

        // Assert
        self::assertSame('(555) 123-4567', $record->customer_phone_number_primary);
        self::assertSame('(555) 123-4567, (555) 987-6543, (555) 111-2222', $record->customer_phone_numbers);
    }

    public function testAddressExtractionFromNestedLocation(): void
    {
        // Arrange
        $serviceTitanData = [
            'id' => 'TEST',
            'location' => [
                'address' => [
                    'street' => '456 Oak Ave',
                    'unit' => 'Suite 100',
                    'city' => 'Springfield',
                    'state' => 'IL',
                    'zip' => '62701',
                    'country' => 'US'
                ]
            ]
        ];

        // Act
        $record = ServiceTitanInvoiceRecordMap::fromServiceTitanData($serviceTitanData);

        // Assert
        self::assertSame('456 Oak Ave', $record->street);
        self::assertSame('Suite 100', $record->unit);
        self::assertSame('Springfield', $record->city);
        self::assertSame('IL', $record->state);
        self::assertSame('62701', $record->zip);
        self::assertSame('US', $record->country);
    }

    public function testAddressExtractionWithMissingLocation(): void
    {
        // Arrange
        $serviceTitanData = [
            'id' => 'TEST'
            // No location data
        ];

        // Act
        $record = ServiceTitanInvoiceRecordMap::fromServiceTitanData($serviceTitanData);

        // Assert
        self::assertNull($record->street);
        self::assertNull($record->unit);
        self::assertNull($record->city);
        self::assertNull($record->state);
        self::assertNull($record->zip);
        self::assertSame('US', $record->country); // Default value
    }

    public function testJobTypeExtractionFromNestedStructure(): void
    {
        // Test with nested jobType structure
        $serviceTitanData = [
            'id' => 'TEST1',
            'jobType' => [
                'name' => 'Emergency Repair'
            ]
        ];
        $record = ServiceTitanInvoiceRecordMap::fromServiceTitanData($serviceTitanData);
        self::assertSame('Emergency Repair', $record->job_type);

        // Test with missing jobType
        $serviceTitanData = ['id' => 'TEST2'];
        $record = ServiceTitanInvoiceRecordMap::fromServiceTitanData($serviceTitanData);
        self::assertNull($record->job_type);
    }

    public function testBusinessUnitMappingToZone(): void
    {
        // Test with businessUnit
        $serviceTitanData = [
            'id' => 'TEST1',
            'businessUnit' => [
                'name' => 'South Division'
            ]
        ];
        $record = ServiceTitanInvoiceRecordMap::fromServiceTitanData($serviceTitanData);
        self::assertSame('South Division', $record->zone);

        // Test without businessUnit
        $serviceTitanData = ['id' => 'TEST2'];
        $record = ServiceTitanInvoiceRecordMap::fromServiceTitanData($serviceTitanData);
        self::assertNull($record->zone);
    }

    public function testSummaryMappingToBothFields(): void
    {
        // Arrange
        $serviceTitanData = [
            'id' => 'TEST',
            'summary' => 'Annual maintenance visit'
        ];

        // Act
        $record = ServiceTitanInvoiceRecordMap::fromServiceTitanData($serviceTitanData);

        // Assert - Summary should be mapped to both fields
        self::assertSame('Annual maintenance visit', $record->summary);
        self::assertSame('Annual maintenance visit', $record->invoice_summary);
    }

    public function testEmptyDataHandling(): void
    {
        // Arrange - Test with completely empty data
        $serviceTitanData = [];

        // Act
        $record = ServiceTitanInvoiceRecordMap::fromServiceTitanData($serviceTitanData);

        // Assert - Should handle gracefully without errors
        self::assertNull($record->invoice_number);
        self::assertSame('', $record->customer_id); // Empty string for customer_id
        self::assertNull($record->total);
        self::assertSame('servicetitan_unknown', $record->hub_plus_import_id);
    }

    public function testNullValueHandling(): void
    {
        // Arrange - Test with explicit null values
        $serviceTitanData = [
            'id' => null,
            'customerId' => null,
            'number' => null,
            'total' => null,
            'invoiceDate' => null,
            'customer' => null,
            'location' => null
        ];

        // Act
        $record = ServiceTitanInvoiceRecordMap::fromServiceTitanData($serviceTitanData);

        // Assert - Should handle null values gracefully
        self::assertNull($record->invoice_number);
        self::assertSame('', $record->customer_id); // Empty string for null customerId
        self::assertNull($record->total);
        self::assertNull($record->first_appointment);
        self::assertNull($record->customer_name);
        self::assertNull($record->street);
        self::assertSame('servicetitan_unknown', $record->hub_plus_import_id);
    }

    /**
     * Test mapping accuracy against a realistic ServiceTitan API response
     */
    public function testMappingAccuracyWithRealisticData(): void
    {
        // Arrange - Realistic ServiceTitan invoice data structure
        $serviceTitanData = [
            'id' => 98765432,
            'number' => 'ST-INV-2025-001',
            'customerId' => 12345678,
            'invoiceDate' => '2025-07-29T14:30:00.000Z',
            'total' => 485.75,
            'balance' => 485.75,
            'summary' => 'Furnace repair and filter replacement',
            'jobNumber' => 'JOB-2025-0729-001',
            'jobType' => [
                'id' => 1001,
                'name' => 'HVAC Service',
                'active' => true
            ],
            'businessUnit' => [
                'id' => 501,
                'name' => 'Residential Services',
                'active' => true
            ],
            'customer' => [
                'id' => 12345678,
                'firstName' => 'Sarah',
                'lastName' => 'Williams',
                'name' => 'Sarah Williams',
                'phoneNumber' => '3125551234',
                'email' => 'sarah.williams@email.com',
                'phoneNumbers' => [
                    [
                        'number' => '3125551234',
                        'type' => 'Mobile'
                    ],
                    [
                        'number' => '7085559876',
                        'type' => 'Home'
                    ]
                ]
            ],
            'location' => [
                'id' => 87654321,
                'name' => 'Williams Residence',
                'address' => [
                    'street' => '789 Maple Street',
                    'unit' => '',
                    'city' => 'Chicago',
                    'state' => 'IL',
                    'zip' => '60614',
                    'country' => 'US'
                ]
            ]
        ];

        // Act
        $record = ServiceTitanInvoiceRecordMap::fromServiceTitanData($serviceTitanData);

        // Assert - Verify all critical mappings
        self::assertSame('ST-INV-2025-001', $record->invoice_number);
        self::assertSame('12345678', $record->customer_id);
        self::assertSame('485.75', $record->total);
        self::assertSame('2025-07-29 14:30:00', $record->first_appointment);
        self::assertSame('JOB-2025-0729-001', $record->job_number);
        self::assertSame('HVAC Service', $record->job_type);
        self::assertSame('Furnace repair and filter replacement', $record->summary);
        self::assertSame('Furnace repair and filter replacement', $record->invoice_summary);
        self::assertSame('Residential Services', $record->zone);

        // Customer information
        self::assertSame('Sarah Williams', $record->customer_name);
        self::assertSame('Sarah', $record->customer_first_name);
        self::assertSame('Williams', $record->customer_last_name);
        self::assertSame('(312) 555-1234', $record->customer_phone_number_primary);
        self::assertSame('(312) 555-1234, (708) 555-9876', $record->customer_phone_numbers);

        // Address
        self::assertSame('789 Maple Street', $record->street);
        self::assertSame('Chicago', $record->city);
        self::assertSame('IL', $record->state);
        self::assertSame('60614', $record->zip);
        self::assertSame('US', $record->country);

        // Metadata
        self::assertSame('servicetitan_98765432', $record->hub_plus_import_id);
    }
}
