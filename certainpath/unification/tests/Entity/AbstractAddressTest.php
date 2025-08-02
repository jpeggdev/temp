<?php

namespace App\Tests\Entity;

use App\Entity\AbstractAddress;
use App\Entity\Address;
use App\Tests\AppTestCase;

class AbstractAddressTest extends AppTestCase
{
    private function getAddress(array $bits): Address
    {
        $address = new Address();
        $address->setAddress1($bits[0]);
        $address->setAddress2($bits[1]);
        $address->setCity($bits[2]);
        $address->setStateCode($bits[3]);
        $address->setPostalCode($bits[4]);
        return $address;
    }

    public function testBusinessDetection(): void
    {
        //Given
        /** @var AbstractAddress $address */
        $address = new Address();
        $address->setAddress1('397 CTY RD C W');
        $address->setAddress2(null);
        $address->setCity('ROSEVILLE');
        $address->setStateCode('MN');
        $address->setPostalCode('55113');
        $address->setPostalCodeShort('55113');
        $address->setCountryCode(null);

        //When
        $address->detectAndAssignBusinessStatus();

        //Then
        self::assertFalse(
            $address->isBusiness(),
        );
    }

    /**
     * @throws \JsonException
     */
    public function testDataSetOne(): void
    {
        $expectedBusinessCount = 0;
        $expectedResidentialCount = 16495;
        $datasetToProcess = 'business_test_dataset_1';
        $this->assertAddressClassification(
            $datasetToProcess,
            $expectedBusinessCount,
            $expectedResidentialCount
        );
    }

    public function testDataSetTwoPoBoxes(): void
    {
        $datasetToProcess = 'business_test_dataset_2_po_boxes';
        $expectedBusinessCount = 8868;
        $expectedResidentialCount = 0;
        $this->assertAddressClassification(
            $datasetToProcess,
            $expectedBusinessCount,
            $expectedResidentialCount
        );
    }

    public function testDataSetThreeCompany(): void
    {
        $datasetToProcess = 'business_test_dataset_3_company';
        $expectedBusinessCount = 14;
        $expectedResidentialCount = 11;
        $this->assertAddressClassification(
            $datasetToProcess,
            $expectedBusinessCount,
            $expectedResidentialCount
        );
    }

    public function testEdgeCaseOne(): void
    {
        //STE
        $address = $this->getAddress(
            [
                "506 STEVENAGE DR",
                "",
                "Pflugerville",
                "78660",
                "TX"
            ]
        );
        $address->detectAndAssignBusinessStatus();
        self::assertFalse($address->isBusiness());

        $address = $this->getAddress(
            [
                "506 STEVENAGE DR STE 200",
                "",
                "Pflugerville",
                "78660",
                "TX"
            ]
        );
        $address->detectAndAssignBusinessStatus();
        self::assertTrue($address->isBusiness());
    }

    public function testEdgeCaseTwo(): void
    {
        //INC
        $address = $this->getAddress(
            [
                "1145 VINCENT PL",
                "",
                "Pflugerville",
                "78660",
                "TX"
            ]
        );
        $address->detectAndAssignBusinessStatus();
        self::assertFalse($address->isBusiness());

        // INC - True positive: Company name with INC suffix
        $address = $this->getAddress(
            [
                "123 Maple Street",
                "Umbrella Corporation, Inc.",
                "Beverly Hills",
                "90210",
                "CA"
            ]
        );
        $address->detectAndAssignBusinessStatus();
        self::assertTrue($address->isBusiness());

        // INC - True positive: Company name with INC in address1
        $address = $this->getAddress(
            [
                "456 Oak Avenue, TechSolutions Inc.",
                "",
                "New York",
                "10001",
                "NY"
            ]
        );
        $address->detectAndAssignBusinessStatus();
        self::assertTrue($address->isBusiness());

        // INC - True positive: Company name with "INC" at end
        $address = $this->getAddress(
            [
                "789 Oak Blvd",
                "Global Dynamics INC",
                "Fake City",
                "75001",
                "TX"
            ]
        );
        $address->detectAndAssignBusinessStatus();
        self::assertTrue($address->isBusiness());

        // INC - True positive: Variations with punctuation
        $address = $this->getAddress(
            [
                "321 Elm Street",
                "Acme Solutions, INC.",
                "Springfield",
                "95110",
                "CA"
            ]
        );
        $address->detectAndAssignBusinessStatus();
        self::assertTrue($address->isBusiness());

        // INC - True positive: Full "INCORPORATED"
        $address = $this->getAddress(
            [
                "654 Birch Lane",
                "MegaCorp Incorporated",
                "Riverside",
                "60601",
                "IL"
            ]
        );
        $address->detectAndAssignBusinessStatus();
        self::assertTrue($address->isBusiness());

        // INC - False positive: Another street name edge case
        $address = $this->getAddress(
            [
                "987 PRINCETON AVE", // Contains "INC" in "PRINCETON"
                "",
                "University Town",
                "08544",
                "NJ"
            ]
        );
        $address->detectAndAssignBusinessStatus();
        self::assertFalse($address->isBusiness());

        // INC - False positive: City name containing "INC"
        $address = $this->getAddress(
            [
                "111 Main Street",
                "",
                "Lincoln", // Contains "INC" in "Lincoln"
                "68508",
                "NE"
            ]
        );
        $address->detectAndAssignBusinessStatus();
        self::assertFalse($address->isBusiness());
    }

    public function testEdgeCaseThree(): void
    {
        //MALL
        $address = $this->getAddress(
            [
                "19806 MALLARD POND TRL",
                "",
                "Pflugerville",
                "78660",
                "TX"
            ]
        );
        $address->detectAndAssignBusinessStatus();
        self::assertFalse($address->isBusiness());

        // MALL - True positive: Actual shopping mall address
        $address = $this->getAddress(
            [
                "123 Main Street",
                "Westfield Shopping Mall",
                "Austin",
                "78701",
                "TX"
            ]
        );
        $address->detectAndAssignBusinessStatus();
        self::assertTrue($address->isBusiness());

        // MALL - True positive: Mall in address1
        $address = $this->getAddress(
            [
                "456 Oak Ridge Mall, Suite 200",
                "",
                "Dallas",
                "75201",
                "TX"
            ]
        );
        $address->detectAndAssignBusinessStatus();
        self::assertTrue($address->isBusiness());

        // MALL - True positive: Shopping center variation
        $address = $this->getAddress(
            [
                "789 Pine Street",
                "Northpark Shopping Center",
                "Houston",
                "77001",
                "TX"
            ]
        );
        $address->detectAndAssignBusinessStatus();
        self::assertTrue($address->isBusiness());

        // MALL - True positive: Business park
        $address = $this->getAddress(
            [
                "321 Commerce Drive",
                "Tech Business Park",
                "Plano",
                "75023",
                "TX"
            ]
        );
        $address->detectAndAssignBusinessStatus();
        self::assertTrue($address->isBusiness());

        // MALL - False positive: Another street name edge case
        $address = $this->getAddress(
            [
                "654 SMALLWOOD CT", // Contains "MALL" in "SMALLWOOD"
                "",
                "Richardson",
                "75080",
                "TX"
            ]
        );
        $address->detectAndAssignBusinessStatus();
        self::assertFalse($address->isBusiness());
    }

    public function testEdgeCaseFour(): void
    {
        // FL abbreviation for FLOOR - False positive: City name containing "FL"
        // 4400 Hidden Lake Xing Pflugerville TX 78660 5551
        $address = $this->getAddress([
            "4400 Hidden Lake Xing",
            "",
            "Pflugerville", // Contains "FL" in "Pflugerville"
            "TX",
            "78660 5551"
        ]);
        $address->detectAndAssignBusinessStatus();
        self::assertFalse($address->isBusiness());

        // FL abbreviation for FLOOR - False positive: Street name containing "FL"
        $address = $this->getAddress([
            "123 Flowery Branch Lane", // Contains "FL" in "Flowery"
            "",
            "Austin",
            "TX",
            "78701"
        ]);
        $address->detectAndAssignBusinessStatus();
        self::assertFalse($address->isBusiness());

        // FL abbreviation for FLOOR - False positive: Another street name edge case
        $address = $this->getAddress([
            "456 Reflective Way", // Contains "FL" in "Reflective"
            "",
            "Dallas",
            "TX",
            "75201"
        ]);
        $address->detectAndAssignBusinessStatus();
        self::assertFalse($address->isBusiness());

        // FL abbreviation for FLOOR - False positive: City name edge case
        $address = $this->getAddress([
            "789 Main Street",
            "",
            "Springfield", // Contains "FL" in "Springfield"
            "IL",
            "62701"
        ]);
        $address->detectAndAssignBusinessStatus();
        self::assertFalse($address->isBusiness());

        // FL abbreviation for FLOOR - True positive: Legitimate floor indicator
        $address = $this->getAddress([
            "123 Business Plaza, 5th FL", // "FL" as floor abbreviation
            "",
            "Houston",
            "TX",
            "77001"
        ]);
        $address->detectAndAssignBusinessStatus();
        self::assertTrue($address->isBusiness());

        // FL abbreviation for FLOOR - True positive: Another floor indicator
        $address = $this->getAddress([
            "456 Corporate Center",
            "FL 3", // "FL" as floor abbreviation in address2
            "San Antonio",
            "TX",
            "78201"
        ]);
        $address->detectAndAssignBusinessStatus();
        self::assertTrue($address->isBusiness());

        // FL abbreviation for FLOOR - True positive: Floor with number
        $address = $this->getAddress([
            "789 Office Tower, FL 12",
            "",
            "El Paso",
            "TX",
            "79901"
        ]);
        $address->detectAndAssignBusinessStatus();
        self::assertTrue($address->isBusiness());

        // FL abbreviation for FLOOR - True positive: Full word "FLOOR"
        $address = $this->getAddress([
            "321 Tech Building, 2nd Floor",
            "",
            "Plano",
            "TX",
            "75023"
        ]);
        $address->detectAndAssignBusinessStatus();
        self::assertTrue($address->isBusiness());

        // FL abbreviation for FLOOR - False positive: State abbreviation edge case
        $address = $this->getAddress([
            "654 Residential Lane",
            "",
            "Miami",
            "FL", // State code "FL" should not trigger
            "33101"
        ]);
        $address->detectAndAssignBusinessStatus();
        self::assertFalse($address->isBusiness());
    }

    public function testEdgeCaseFive(): void
    {
        //CO abbreviation for COMPANY
        $address = $this->getAddress(
            [
                '19409 NICOLE LN',
                '',
                'Pflugerville',
                'TX',
                '786603798'
            ]
        );
        $address->detectAndAssignBusinessStatus();
        self::assertFalse($address->isBusiness());

        // CO abbreviation for COMPANY - False positive: Another street name edge case
        $address = $this->getAddress([
            '123 Nicolet Drive', // Contains "CO" in "Sycomore"
            '',
            'Austin',
            'TX',
            '78701'
        ]);
        $address->detectAndAssignBusinessStatus();
        self::assertFalse($address->isBusiness());

        // CO abbreviation for COMPANY - False positive: City name containing "CO"
        $address = $this->getAddress([
            '456 Main Street',
            '',
            'Frisco', // Contains "CO" in "Frisco"
            'TX',
            '75034'
        ]);
        $address->detectAndAssignBusinessStatus();
        self::assertFalse($address->isBusiness());

        // CO abbreviation for COMPANY - False positive: Another city edge case
        $address = $this->getAddress([
            '789 Oak Avenue',
            '',
            'Conroe', // Contains "CO" in "Conroe"
            'TX',
            '77301'
        ]);
        $address->detectAndAssignBusinessStatus();
        self::assertFalse($address->isBusiness());

        // CO abbreviation for COMPANY - False positive: State abbreviation edge case
        $address = $this->getAddress([
            '321 Residential Street',
            '',
            'Denver',
            'CO', // State code "CO" should not trigger
            '80202'
        ]);
        $address->detectAndAssignBusinessStatus();
        self::assertFalse($address->isBusiness());

        // CO abbreviation for COMPANY - True positive: Legitimate company indicator
        $address = $this->getAddress([
            '123 Residential Lane',
            'Umbrella Co', // "Co" as company abbreviation
            'Houston',
            'TX',
            '77001'
        ]);
        $address->detectAndAssignBusinessStatus();
        self::assertTrue($address->isBusiness());

        // CO abbreviation for COMPANY - True positive: Another company format
        $address = $this->getAddress([
            '456 Residential Blvd',
            'Smith & Co',
            'Dallas',
            'TX',
            '75201'
        ]);
        $address->detectAndAssignBusinessStatus();
        self::assertTrue($address->isBusiness());

        // CO abbreviation for COMPANY - True positive: Company in address1
        $address = $this->getAddress([
            '789 Tech Center, Acme Co',
            '',
            'San Antonio',
            'TX',
            '78201'
        ]);
        $address->detectAndAssignBusinessStatus();
        self::assertTrue($address->isBusiness());

        // CO abbreviation for COMPANY - True positive: Full word "COMPANY"
        $address = $this->getAddress([
            '321 Residential Park',
            'ABC Company',
            'El Paso',
            'TX',
            '79901'
        ]);
        $address->detectAndAssignBusinessStatus();
        self::assertTrue($address->isBusiness());

        // CO abbreviation for COMPANY - True positive: Multiple formats
        $address = $this->getAddress([
            '654 Residential Drive, Johnson Co',
            '',
            'Plano',
            'TX',
            '75023'
        ]);
        $address->detectAndAssignBusinessStatus();
        self::assertTrue($address->isBusiness());

        // CO abbreviation for COMPANY - True positive: Company with "and"
        $address = $this->getAddress([
            '987 Beer Way',
            'Miller and Co',
            'Arlington',
            'TX',
            '76001'
        ]);
        $address->detectAndAssignBusinessStatus();
        self::assertTrue($address->isBusiness());
    }

    public function testEdgeCaseSix(): void
    {
        $address = $this->getAddress(
            [
                '38th street',
                '',
                'Pflugerville',
                'TX',
                '786603798'
            ]
        );
        $address->detectAndAssignBusinessStatus();
        self::assertFalse($address->isBusiness());
    }

    public function testEdgeCaseSeven(): void
    {
        $address = $this->getAddress([
            '11662 CO. RD. 54',
            '',
            'DAPHNE',
            'AL',
            '36526'
        ]);

        $address->detectAndAssignBusinessStatus();
        self::assertFalse($address->isBusiness());
    }

    public function testEdgeCaseEight(): void
    {
        $address = $this->getAddress([
            '136 ROSANKY CATTLE COMPANY RD',
            '',
            'SMITHVILLE',
            'TX',
            '78957'
        ]);
        $address->detectAndAssignBusinessStatus();
        self::assertFalse($address->isBusiness());
    }

    public function testEdgeCaseNine(): void
    {
        $address = $this->getAddress([
            "10060 CO. ROAD 133",
            "",
            "KAUFMAN",
            "75142",
            "TX"
        ]);
        $address->detectAndAssignBusinessStatus();
        self::assertFalse($address->isBusiness());
    }

    // Strong Business Indicators - PO Box Detection Tests
    public function testPoBoxDetection(): void
    {
        $address = new Address();
        $address->setAddress1('123 Main St');
        $address->setAddress2('PO BOX 456');
        $address->setCity('Anytown');
        $address->setStateCode('CA');
        $address->setPostalCode('90210');

        $address->detectAndAssignBusinessStatus();

        self::assertTrue($address->isBusiness());
    }

    public function testPoBoxVariations(): void
    {
        $variations = ['PO BOX 123', 'P.O. BOX 456', 'P O BOX 789', 'POST OFFICE BOX 101'];

        foreach ($variations as $variation) {
            $address = new Address();
            $address->setAddress1($variation);
            $address->setCity('Test City');
            $address->setStateCode('NY');
            $address->setPostalCode('12345');

            $address->detectAndAssignBusinessStatus();

            self::assertTrue($address->isBusiness(), "Failed for variation: {$variation}");
        }
    }

    // Strong Business Indicators - Commercial Keywords Tests
    public function testCommercialKeywords(): void
    {
        $keywords = ['SUITE 300', 'STE 15', 'UNIT B', '# 205', 'DEPT A', 'DEPARTMENT 10', 'FLOOR 5', 'FL 12'];

        foreach ($keywords as $keyword) {
            $address = new Address();
            $address->setAddress1('789 Market St');
            $address->setAddress2($keyword);
            $address->setCity('Business City');
            $address->setStateCode('TX');
            $address->setPostalCode('75001');

            $address->detectAndAssignBusinessStatus();

            self::assertTrue($address->isBusiness(), "Failed for keyword: {$keyword}");
        }
    }

    public function testCommercialKeywordsInAddress1(): void
    {
        $address = new Address();
        $address->setAddress1('789 Market St Suite 300');
        $address->setCity('Business City');
        $address->setStateCode('TX');
        $address->setPostalCode('75001');

        $address->detectAndAssignBusinessStatus();

        self::assertTrue($address->isBusiness());
    }

    // Strong Business Indicators - Business Name Suffixes Tests
    public function testBusinessNameSuffixes(): void
    {
        $suffixes = [
            'INC',
            'LLC',
            'LTD',
            'CORP',
            'CO',
            'COMPANY',
            'ASSOCIATES',
            'CORPORATION',
            'LIMITED',
            'INCORPORATED'
        ];

        foreach ($suffixes as $suffix) {
            $address = new Address();
            $address->setAddress1("ABC {$suffix}");
            $address->setAddress2('567 Commerce Ave');
            $address->setCity('Corporate City');
            $address->setStateCode('DE');
            $address->setPostalCode('19801');

            $address->detectAndAssignBusinessStatus();

            self::assertTrue($address->isBusiness(), "Failed for suffix: {$suffix}");
        }
    }

    public function testBusinessNameSuffixesInAddress1(): void
    {
        $address = new Address();
        $address->setAddress1('567 Commerce Ave, TechCorp LLC');
        $address->setCity('Corporate City');
        $address->setStateCode('DE');
        $address->setPostalCode('19801');

        $address->detectAndAssignBusinessStatus();

        self::assertTrue($address->isBusiness());
    }

    // Strong Business Indicators - Commercial Zone Indicators Tests
    public function testCommercialZoneIndicators(): void
    {
        $zones = [
            'INDUSTRIAL PARK',
            'BUSINESS PARK',
            'BUSINESS CENTER',
            'PLAZA',
            'MALL',
            'SHOPPING CENTER',
            'OFFICE PARK'
        ];

        foreach ($zones as $zone) {
            $address = new Address();
            $address->setAddress1("123 Gateway {$zone}");
            $address->setCity('Commerce Town');
            $address->setStateCode('OH');
            $address->setPostalCode('44101');

            $address->detectAndAssignBusinessStatus();

            self::assertTrue($address->isBusiness(), "Failed for zone: {$zone}");
        }
    }

    public function testCommercialZoneIndicatorsInAddress2(): void
    {
        $address = new Address();
        $address->setAddress1('123 Gateway Ave');
        $address->setAddress2('Technology Business Park');
        $address->setCity('Commerce Town');
        $address->setStateCode('OH');
        $address->setPostalCode('44101');

        $address->detectAndAssignBusinessStatus();

        self::assertTrue($address->isBusiness());
    }

    // Moderate Business Indicators - Multiple Units Tests
    public function testMultipleUnitsModerateConfidence(): void
    {
        $address = new Address();
        $address->setAddress1('456 Oak St');
        $address->setAddress2('Unit B');
        $address->setCity('Suburban Town');
        $address->setStateCode('FL');
        $address->setPostalCode('33101');

        $address->detectAndAssignBusinessStatus();

        self::assertTrue($address->isBusiness());
    }

    // Moderate Business Indicators - Commercial Street Names Tests
    public function testCommercialStreetNames(): void
    {
        $streetTerms = ['COMMERCE', 'INDUSTRIAL', 'BUSINESS', 'CORPORATE', 'TECHNOLOGY', 'ENTERPRISE'];
        $streetTypes = ['ST', 'STREET', 'AVE', 'AVENUE', 'BLVD', 'BOULEVARD', 'DR', 'DRIVE', 'WAY', 'RD', 'ROAD'];

        foreach ($streetTerms as $term) {
            foreach ($streetTypes as $type) {
                $address = new Address();
                $address->setAddress1("789 {$term} {$type}");
                $address->setCity('Industrial City');
                $address->setStateCode('MI');
                $address->setPostalCode('48201');

                $address->detectAndAssignBusinessStatus();

                self::assertTrue($address->isBusiness(), "Failed for street: {$term} {$type}");
            }
        }
    }

    public function testCommercialStreetNameSpecificExample(): void
    {
        $address = new Address();
        $address->setAddress1('789 Industrial Blvd');
        $address->setCity('Manufacturing City');
        $address->setStateCode('IN');
        $address->setPostalCode('46201');

        $address->detectAndAssignBusinessStatus();

        self::assertTrue($address->isBusiness());
    }

    // Default to Residential Tests
    public function testDefaultToResidential(): void
    {
        $address = new Address();
        $address->setAddress1('123 Maple Street');
        $address->setCity('Hometown');
        $address->setStateCode('WI');
        $address->setPostalCode('53001');

        $address->detectAndAssignBusinessStatus();

        self::assertFalse($address->isBusiness());
    }

    public function testResidentialWithCommonTerms(): void
    {
        $address = new Address();
        $address->setAddress1('456 Elm Avenue');
        $address->setAddress2('Apt 2B');
        $address->setCity('Residential Area');
        $address->setStateCode('OR');
        $address->setPostalCode('97201');

        $address->detectAndAssignBusinessStatus();

        self::assertFalse($address->isBusiness());
    }

    // Edge Cases and Combinations
    public function testMultipleBusinessIndicators(): void
    {
        $address = new Address();
        $address->setAddress1('123 Commerce Blvd');
        $address->setAddress2('Suite 100, TechCorp LLC');
        $address->setCity('Business District');
        $address->setStateCode('GA');
        $address->setPostalCode('30301');

        $address->detectAndAssignBusinessStatus();

        self::assertTrue($address->isBusiness());
    }

    public function testCaseInsensitiveDetection(): void
    {
        $address = new Address();
        $address->setAddress1('789 main st');
        $address->setAddress2('suite 200');
        $address->setCity('anytown');
        $address->setStateCode('az');
        $address->setPostalCode('85001');

        $address->detectAndAssignBusinessStatus();

        self::assertTrue($address->isBusiness());
    }

    public function testMixedCaseDetection(): void
    {
        $address = new Address();
        $address->setAddress1('456 Business Park Dr');
        $address->setAddress2('Unit 15b');
        $address->setCity('Commerce City');
        $address->setStateCode('CO');
        $address->setPostalCode('80022');

        $address->detectAndAssignBusinessStatus();

        self::assertTrue($address->isBusiness());
    }

    public function testEmptyAddress2WithBusinessIndicators(): void
    {
        $address = new Address();
        $address->setAddress1('100 Industrial Way');
        $address->setAddress2(null);
        $address->setCity('Factory Town');
        $address->setStateCode('AL');
        $address->setPostalCode('35201');

        $address->detectAndAssignBusinessStatus();

        self::assertTrue($address->isBusiness());
    }

    public function testOnlyAddress1WithPoBox(): void
    {
        $address = new Address();
        $address->setAddress1('PO BOX 12345');
        $address->setAddress2(null);
        $address->setCity('Mail City');
        $address->setStateCode('NV');
        $address->setPostalCode('89101');

        $address->detectAndAssignBusinessStatus();

        self::assertTrue($address->isBusiness());
    }

    // Test partial matches that should not trigger business detection
    public function testPartialMatchesShouldNotTrigger(): void
    {
        $address = new Address();
        $address->setAddress1('123 Enterprise Court'); // Contains "Enterprise" but in different context
        $address->setCity('Residential Suburb');
        $address->setStateCode('VA');
        $address->setPostalCode('22101');

        $address->detectAndAssignBusinessStatus();

        // This should still trigger as "ENTERPRISE" is in our commercial street terms
        self::assertTrue($address->isBusiness());
    }

    public function testResidentialStreetWithBusinessWordInName(): void
    {
        $address = new Address();
        $address->setAddress1('123 Corporate Lane'); // Contains "Corporate" but as part of street name
        $address->setCity('Suburban Area');
        $address->setStateCode('NC');
        $address->setPostalCode('27601');

        $address->detectAndAssignBusinessStatus();

        // This should trigger as "CORPORATE" is in our commercial street terms
        self::assertTrue($address->isBusiness());
    }

    /**
     * @param string $datasetToProcess
     * @param int $expectedBusinessCount
     * @param int $expectedResidentialCount
     * @return void
     */
    private function assertAddressClassification(
        string $datasetToProcess,
        int $expectedBusinessCount,
        int $expectedResidentialCount
    ): void {
        /** @var Address[] $businessAddresses */
        $businessAddresses = [];
        /** @var Address[] $residentialAddresses */
        $residentialAddresses = [];
        // Read the CSV file
        $csvPath = __DIR__ . '/../Files/' . $datasetToProcess . '.csv';
        self::assertFileExists($csvPath, 'CSV file should exist');

        $file = fopen($csvPath, 'rb');
        self::assertNotFalse($file, 'Should be able to open CSV file');

        // Read header row
        $header = fgetcsv($file);
        self::assertNotFalse($header, 'Should be able to read header row');

        $totalProcessed = 0;

        // Process each row
        while (($row = fgetcsv($file)) !== false) {
            $totalProcessed++;
            // Map CSV columns to address components
            // Expected columns: is_business,address1,address2,city,state_code,postal_code_short,postal_code
            $expectedIsBusiness = $row[0] === 'true';
            $addressData = [
                $row[1], // address1
                $row[2], // address2
                $row[3], // city
                $row[4], // state_code
                $row[5]  // postal_code_short (using this instead of full postal_code)
            ];

            // Create address record using getAddress
            $address = $this->getAddress($addressData);

            // Detect business status
            $address->detectAndAssignBusinessStatus();

            // Add to appropriate array based on detection result
            if ($address->isBusiness()) {
                $businessAddresses[] = $address;
            } else {
                $residentialAddresses[] = $address;
            }
        }

        fclose($file);

        // Make assertions on array counts
        self::assertGreaterThan(0, $totalProcessed, 'Should have processed at least one row');
        self::assertCount(
            $expectedBusinessCount,
            $businessAddresses,
            'Business addresses count should match expected'
        );
        self::assertCount(
            $expectedResidentialCount,
            $residentialAddresses,
            'Residential addresses count should match expected'
        );

        // Verify total count matches
        self::assertEquals(
            $totalProcessed,
            count($businessAddresses) + count($residentialAddresses),
            'Total addresses should equal business + residential addresses'
        );
//        foreach ($businessAddresses as $businessAddress) {
//            $this->debug($businessAddress->getAsArray());
//        }
    }
}
