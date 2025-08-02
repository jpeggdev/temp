<?php

namespace App\Services\AddressVerification;

use App\Entity\AbstractAddress;
use Psr\Log\LoggerInterface;
use Symfony\Component\Serializer\SerializerInterface;

use function App\Functions\app_getPostalCodeShort;

class MockAddressVerificationService implements AddressVerificationServiceInterface
{
    public const API_TYPE = 'mock';

    public function __construct(
        private readonly SerializerInterface $serializer,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function getApiType(): string
    {
        return self::API_TYPE;
    }

    public function verifyAndNormalize(AbstractAddress $address): AbstractAddress
    {
        $countryCode = 'USA';
        if (!$address->getPostalCode()) {
            $this->logger->warning('Address has no postal code', [
                'address' => $address->getAddress1(),
            ]);
            $postalCode = '';
        } else {
            $postalCode = $address->getPostalCode();
        }
        $apiResponse = $this->serializer->serialize([ ], 'json');
        $address->setCountryCode($countryCode);
        $address->setPostalCode($postalCode);
        $address->setVerifiedAt(date_create_immutable());
        $address->incrementVerificationAttempts();
        $address->setApiResponse($apiResponse);
        $address->setApiType($this->getApiType());

        return $address;
    }
}
