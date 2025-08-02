<?php

namespace App\Tests\Services;

use App\Services\AddressVerification\USPSAddressVerificationService;
use App\Tests\FunctionalTestCase;
use App\ValueObjects\AddressObject;

class USPSAddressVerificationServiceTest extends FunctionalTestCase
{
    /**
     * @group remoteResources
     */
    public function testRefreshOauth(): void
    {
        $addressVerificationService = $this->getUSPSAddressVerificationService();
        $this->assertNotEmpty($addressVerificationService->getAccessToken());
        $this->assertNotEmpty($addressVerificationService->getAccessTokenExpiresAt());
    }

    /**
     * @group remoteResources
     */
    public function testVerifyAddress(): void
    {
        $addressObject = $this->getAddressObject();
        $verifiedAddressObject = $this->getUSPSAddressVerificationService()->verifyAddress($addressObject);
        $this->assertTrue($verifiedAddressObject->isVerified());
    }

    private function getUSPSAddressVerificationService(): USPSAddressVerificationService
    {
        return $this->getService(
            USPSAddressVerificationService::class
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
