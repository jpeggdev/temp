<?php

namespace App\Tests\Services;

use App\DTO\Request\Address\RestrictedAddressEditDTO;
use App\Entity\Address;
use App\Entity\RestrictedAddress;
use App\Exceptions\DomainException\RestrictedAddress\RestrictedAddressAlreadyExistsException;
use App\Services\Address\AddressService;
use App\Tests\FunctionalTestCase;
use App\ValueObjects\AddressObject;

class AddressServiceTest extends FunctionalTestCase
{
    public function testVerifyAndNormalizeFindsExistingAddress(): void
    {
        $company = $this->initializeCompany();
        $existingAddress = (new Address())->fromValueObject($this->getAddressObject());
        $existingAddress->setCompany($company);
        $existingAddress = $this->getAddressService()->verifyAndNormalize($existingAddress);
        $this->getAddressRepository()->save($existingAddress);

        $incomingAddress = (new Address())->fromValueObject(new AddressObject([
            'name' => 'CertainPath',
            'address1' => '15301 Spectrum Dr.',
            'address2' => 'STE #200',
            'city' => 'Addison',
            'stateCode' => 'TX',
            'postalCode' => '75001',
        ]));
        $incomingAddress->setCompany($company);
        $incomingAddress = $this->getAddressService()->verifyAndNormalize($incomingAddress);
        $this->getAddressRepository()->save($incomingAddress);

        $this->assertSame($existingAddress->getExternalId(), $incomingAddress->getExternalId());
        $this->assertSame($existingAddress, $incomingAddress);
        $this->assertCount(1, $this->getAddressRepository()->findAll());
    }

    /**
     * @dataProvider addressFieldProvider
     */
    public function testSanitizeAddressField(string $input, string $expected): void
    {
        $this->assertEquals(
            $expected,
            AddressService::sanitizeAddressField($input)
        );
    }

    public function addressFieldProvider(): array
    {
        return [
            'basic street abbreviation' => [
                'input' => '123 Main Street',
                'expected' => '123 MAIN ST'
            ],
            'multiple abbreviations' => [
                'input' => '456 North Avenue Suite 789',
                'expected' => '456 N AVE STE 789'
            ],
            'special characters' => [
                'input' => '789-A West Boulevard #100',
                'expected' => '789A W BLVD 100'
            ],
            'multiple spaces' => [
                'input' => '  100   East    Court   ',
                'expected' => '100 E CT'
            ],
            'mixed case' => [
                'input' => 'sOuThEaSt PlAcE',
                'expected' => 'SE PL'
            ],
            'apartment abbreviation' => [
                'input' => '321 South Road Apartment 5B',
                'expected' => '321 S RD APT 5B'
            ],
            'complex directionals' => [
                'input' => '789 Northwest Highway Suite 300',
                'expected' => '789 NW HWY STE 300'
            ],
            'multiple unit identifiers' => [
                'input' => '555 Southwest Boulevard Building 2 Floor 3',
                'expected' => '555 SW BLVD BLDG 2 FL 3'
            ],
            'department and room' => [
                'input' => '999 East Parkway Department 100 Room 45',
                'expected' => '999 E PKWY DEPT 100 RM 45'
            ],
            'circle and terrace' => [
                'input' => '123 Victory Circle Terrace',
                'expected' => '123 VICTORY CIR TER'
            ],
            'multiple directionals' => [
                'input' => 'Northeast South Lane',
                'expected' => 'NE S LN'
            ],
            'square and place' => [
                'input' => '456 Market Square Place',
                'expected' => '456 MARKET SQ PL'
            ],
            'highway with numbers' => [
                'input' => '7890 State Highway 121',
                'expected' => '7890 STATE HWY 121'
            ],
            'way and drive' => [
                'input' => '1234 Sunset Way Drive',
                'expected' => '1234 SUNSET WAY DR'
            ],
            'multiple special chars with invalid replacement' => [
                'input' => '42-A/B North-South Avenue #200-B',
                'expected' => '42AB NORTHSOUTH AVE 200B'
            ],
        ];
    }

    /**
     * @dataProvider addressObjectProvider
     */
    public function testSanitizeAddressAddressObjectForKey(array $input, string $expected): void
    {
        $input['address1'] = AddressService::sanitizeAddressField($input['address1']);
        $input['address2'] = AddressService::sanitizeAddressField($input['address2']);
        $addressObject = new AddressObject($input);

        $this->assertEquals(
            $expected,
            $addressObject->getKey()
        );
    }

    public function addressObjectProvider(): array
    {
        return [
            'basic address object' => [
                'input' => [
                    'name' => 'Test Company',
                    'address1' => '123 Main Street',
                    'address2' => 'Suite 100',
                    'city' => 'Addison',
                    'stateCode' => 'TX',
                    'postalCode' => '75001',
                ],
                'expected' => '123mainstste100addisontx75001'
            ],
            'basic address object duplicate' => [
                'input' => [
                    'name' => 'Test Company',
                    'address1' => '123 Main St',
                    'address2' => 'Suite 100',
                    'city' => 'Addison',
                    'stateCode' => 'TX',
                    'postalCode' => '75001',
                ],
                'expected' => '123mainstste100addisontx75001'
            ],
            'complex address object' => [
                'input' => [
                    'name' => 'Tech Corp',
                    'address1' => '456 Northeast Boulevard Building 2',
                    'address2' => 'Department 300',
                    'city' => 'Dallas',
                    'stateCode' => 'TX',
                    'postalCode' => '75202',
                ],
                'expected' => '456neblvdbldg2dept300dallastx75202'
            ],
            'special chars address object' => [
                'input' => [
                    'name' => 'Global Inc',
                    'address1' => '789-A South Avenue #500',
                    'address2' => 'Floor 3',
                    'city' => 'Austin',
                    'stateCode' => 'TX',
                    'postalCode' => '73301',
                ],
                'expected' => '789asave500fl3austintx73301'
            ],
            'special chars address object duplicate 1' => [
                'input' => [
                    'name' => 'Global Inc',
                    'address1' => '789A South Avenue #500',
                    'address2' => 'Floor 3',
                    'city' => 'Austin',
                    'stateCode' => 'TX',
                    'postalCode' => '73301',
                ],
                'expected' => '789asave500fl3austintx73301'
            ],
            'special chars address object duplicate 2' => [
                'input' => [
                    'name' => 'Global Inc',
                    'address1' => '789A South Ave #500',
                    'address2' => 'Floor #3',
                    'city' => 'Austin',
                    'stateCode' => 'TX',
                    'postalCode' => '73301',
                ],
                'expected' => '789asave500fl3austintx73301'
            ],
            'multiple directionals with unit' => [
                'input' => [
                    'name' => 'Acme Corp',
                    'address1' => '1000 Northwest Southeast Parkway',
                    'address2' => 'Apartment 42B',
                    'city' => 'Houston',
                    'stateCode' => 'TX',
                    'postalCode' => '77001',
                ],
                'expected' => '1000nwsepkwyapt42bhoustontx77001'
            ],
            'highway address with building' => [
                'input' => [
                    'name' => 'Highway Business',
                    'address1' => '5555 State Highway 121 Building A',
                    'address2' => 'Room 101',
                    'city' => 'Plano',
                    'stateCode' => 'TX',
                    'postalCode' => '75024',
                ],
                'expected' => '5555statehwy121bldgarm101planotx75024'
            ],
            'complex unit identifiers' => [
                'input' => [
                    'name' => 'Multi Office Corp',
                    'address1' => '888 Victory Place Suite 400',
                    'address2' => 'Department 55 Floor 12',
                    'city' => 'Fort Worth',
                    'stateCode' => 'TX',
                    'postalCode' => '76102',
                ],
                'expected' => '888victoryplste400dept55fl12fortworthtx76102'
            ],
            'special characters with spaces' => [
                'input' => [
                    'name' => 'Space Corp',
                    'address1' => '777-B  South  West  Circle   #200-A',
                    'address2' => 'Building  3   Suite  100',
                    'city' => 'Arlington',
                    'stateCode' => 'TX',
                    'postalCode' => '76010',
                ],
                'expected' => '777bswcir200abldg3ste100arlingtontx76010'
            ],
            'mixed abbreviations' => [
                'input' => [
                    'name' => 'Mix Corp',
                    'address1' => '444 Northeast Boulevard Street',
                    'address2' => 'Suite 800 Department X',
                    'city' => 'Irving',
                    'stateCode' => 'TX',
                    'postalCode' => '75039',
                ],
                'expected' => '444neblvdstste800deptxirvingtx75039'
            ],
        ];
    }

    public function testThrowExceptionOnDuplicateRestrictedAddressExternalId(): void
    {
        $this->expectException(RestrictedAddressAlreadyExistsException::class);
        $restrictedAddress1 = (new RestrictedAddress())->fromValueObject($this->getAddressObject());
        $restrictedAddress2 = clone $restrictedAddress1;

        $restrictedAddressEditDTO = new RestrictedAddressEditDTO();
        $restrictedAddressEditDTO->address1 = 'address1';
        $restrictedAddressEditDTO->city = 'city';
        $restrictedAddressEditDTO->stateCode = 'NY';
        $restrictedAddressEditDTO->postalCode = '00001';
        $restrictedAddressEditDTO->countryCode  = 'US';

        $this->getAddressService()->editRestrictedAddress(
            $restrictedAddress1,
            $restrictedAddressEditDTO
        );

        $this->getAddressService()->editRestrictedAddress(
            $restrictedAddress2,
            $restrictedAddressEditDTO
        );
    }

    public function testAddToDoNotMail(): void
    {
        $company = $this->initializeCompany();
        $addressObject = $this->getAddressObject();
        $address = (new Address())->fromValueObject($addressObject);

        $address->setCompany($company);
        $this->getAddressRepository()->save($address);

        $restrictedAddress = (new RestrictedAddress())->fromValueObject($addressObject);
        $this->getAddressRepository()->save($restrictedAddress);
        $this->getAddressService()->propagateDoNotMailForRestrictedAddress($restrictedAddress);

        $this->getAddressRepository()->refresh($address);

        $this->assertTrue($address->isDoNotMail());
    }

    private function getAddressService(): AddressService
    {
        return $this->getService(
            AddressService::class
        );
    }

    private function getAddressObject(): AddressObject
    {
        return new AddressObject([
            'name' => 'CertainPath',
            'address1' => '15301 Spectrum Drive',
            'address2' => 'Suite 200',
            'city' => 'Addison',
            'stateCode' => 'TX',
            'postalCode' => '75001',
        ]);
    }
}
