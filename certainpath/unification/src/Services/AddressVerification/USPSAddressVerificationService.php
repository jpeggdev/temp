<?php

namespace App\Services\AddressVerification;

use App\Entity\AbstractAddress;
use App\Entity\Setting;
use App\Exceptions\Smarty\AddressVerificationFailedException;
use App\Exceptions\USPS\USPSAddressVerificationRateLimitException;
use App\Exceptions\USPS\USPSAddressVerificationValidationException;
use App\Repository\SettingRepository;
use App\ValueObjects\AddressObject;
use Exception;
use JsonException;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use function App\Functions\app_getTimestampInMilliseconds;

class USPSAddressVerificationService implements AddressVerificationServiceInterface
{
    public const API_TYPE = 'usps';

    private HttpClientInterface $httpClient;
    private ?Setting $accessTokenSetting;
    private ?string $accessToken;
    private int $accessTokenExpiresAt;
    private bool $isRateLimitExceeded = false;

    private const USPS_OAUTH_ENDPOINT = 'https://api.usps.com/oauth2/v3/token';
    private const USPS_TOKEN_SETTING_NAME = 'usps_api_oauth_access_token';
    private const USPS_OAUTH_PAYLOAD = [
        'access_token' => null,
        'token_type' => null,
        'issued_at' => 0,
        'expires_in' => 0,
        'status' => null,
        'scope' => null,
        'issuer' => null,
        'client_id' => null,
        'application_name' => null,
        'api_products' => null,
        'public_key' => null,
    ];

    private const USPS_ADDRESSES_ENDPOINT = 'https://api.usps.com/addresses/v3/address';
    private const USPS_ADDRESS_REQUEST_PAYLOAD = [
        'firm' => '',
        'streetAddress' => '',
        'secondaryAddress' => '',
        'city' => '',
        'state' => '',
        'urbanization' => '',
        'ZIPCode' => '',
        'ZIPPlus4' => '',
    ];

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     * @throws JsonException
     */
    public function __construct(
        private readonly string $uspsApiClientId,
        private readonly string $uspsApiClientSecret,
        private readonly SettingRepository $settingRepository,
        private readonly LoggerInterface $logger,
    ) {
        $this->httpClient = HttpClient::create();
        $this->accessTokenSetting = $this->settingRepository->findSettingByName(self::USPS_TOKEN_SETTING_NAME);
        if (!$this->accessTokenSetting) {
            $this->accessTokenSetting = (new Setting())
                ->setName(self::USPS_TOKEN_SETTING_NAME)
                ->setValue(json_encode(self::USPS_OAUTH_PAYLOAD, JSON_THROW_ON_ERROR))
                ->setType('json');
            $this->settingRepository->save($this->accessTokenSetting);
        }
        $arr = json_decode($this->accessTokenSetting->getValue(), true, 10, JSON_THROW_ON_ERROR);
        $this->accessToken = $arr['access_token'];
        $this->accessTokenExpiresAt = ($arr['issued_at'] + $arr['expires_in']);

        if (
            empty($this->accessToken) ||
            $this->isExpired($this->accessTokenExpiresAt)
        ) {
            $this->refreshOauth();
        }
    }

    public function getApiType(): string
    {
        return self::API_TYPE;
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     * @throws JsonException
     */
    private function refreshOauth(): void
    {
        $response = $this->httpClient->request('POST', self::USPS_OAUTH_ENDPOINT, [
            'json' => [
                "grant_type" => "client_credentials",
                "client_id" => $this->uspsApiClientId,
                "client_secret" => $this->uspsApiClientSecret,
            ],
        ]);

        if ($response->getStatusCode() === 200) {
            $data = $response->toArray();

            if (!empty($data['access_token'])) {
                $this->accessTokenSetting->setValue(json_encode($data, JSON_THROW_ON_ERROR));
                $this->accessTokenSetting = $this->settingRepository->save($this->accessTokenSetting);

                $arr = json_decode($this->accessTokenSetting->getValue(), true, 10, JSON_THROW_ON_ERROR);
                $this->accessToken = $arr['access_token'];
                $this->accessTokenExpiresAt = ($arr['issued_at'] + $arr['expires_in']);
            }
        }
    }

    /**
     * @throws AddressVerificationFailedException
     */
    public function verifyAndNormalize(AbstractAddress $address): AbstractAddress
    {
        try {
            $addressObject = AddressObject::fromEntity($address);
            $processedAddressObject = $addressObject;
            if (!$this->isRateLimitExceeded()) {
                $processedAddressObject = $this->verifyAddress($addressObject);
            }

            $address->incrementVerificationAttempts();
            $address->setExternalId($processedAddressObject->getKey());
            $address->setAddress1($processedAddressObject->address1);
            $address->setAddress2($processedAddressObject->address2);
            $address->setCity($processedAddressObject->city);
            $address->setStateCode($processedAddressObject->stateCode);
            $address->setPostalCode($processedAddressObject->postalCode);
            $address->setCountryCode($processedAddressObject->countryISOCode);
            $address->setBusiness($processedAddressObject->isBusiness);
            $address->setVacant($processedAddressObject->isVacant);
            $address->setApiResponse($processedAddressObject->toJson());
            if ($processedAddressObject->isVerified()) {
                $address->setVerifiedAt(date_create_immutable());
            }

            return $address;
        } catch (Exception) {
            throw new AddressVerificationFailedException();
        }
    }

    /**
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     * @throws USPSAddressVerificationRateLimitException
     * @throws USPSAddressVerificationValidationException
     * @throws JsonException
     */
    public function verifyAddress(AddressObject $addressObject): AddressObject
    {
        $address = [
            'firm' => $addressObject->name,
            'streetAddress' => $addressObject->address1,
            'secondaryAddress' => $addressObject->address2,
            'city' => $addressObject->city,
            'state' => $addressObject->stateCode,
            'ZIPCode' => $addressObject->getPostalCodeShort(),
        ];
        $encodedAddress = json_encode($address);
        echo date('Y-m-d H:i:s') . ': Verifying: ' . $encodedAddress . PHP_EOL;

        $response = $this->httpClient->request('GET', self::USPS_ADDRESSES_ENDPOINT, [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->getAccessToken(),
            ],
            'query' => array_filter(array_merge(
                self::USPS_ADDRESS_REQUEST_PAYLOAD,
                $address
            )),
        ]);

        $statusCode = $response->getStatusCode();

        if ($statusCode === 429) {
            $errorHeadersArray = $response->getHeaders(false);
            $retryAfter = $errorHeadersArray['retry-after'][0] ?? 'unknown';
            $retryAfterString = 'RETRY-AFTER: ' . $retryAfter;
            $errorHeadersString = json_encode($errorHeadersArray);
            $errorResponse = json_encode($response->toArray(false));
            $fullErrorString = $retryAfterString
                . ' - '
                . $errorHeadersString
                . ' - '
                . $errorResponse;
            $this->logger->error(
                'USPS rate limit exceeded. '
                . $fullErrorString
            );
            $this->isRateLimitExceeded = true;
            throw new USPSAddressVerificationRateLimitException(
                $fullErrorString
            );
        }

        if ($statusCode === 400) {
            $errorResponse = json_encode($response->toArray(false));
            $this->logger->error(sprintf(
                '%s: %s: %s',
                __CLASS__,
                $encodedAddress,
                $errorResponse
            ));

            throw new USPSAddressVerificationValidationException(
                $encodedAddress
                . ': '
                .
                $errorResponse
            );
        }

        if ($statusCode === 200) {
            $data = $response->toArray();
            $addressObject->name = $data['firm'];
            $addressObject->address1 = $data['address']['streetAddress'];
            $addressObject->address2 = $data['address']['secondaryAddress'];
            $addressObject->city = $data['address']['city'];
            $addressObject->cityAbbreviation = $data['address']['cityAbbreviation'];
            $addressObject->stateCode = $data['address']['state'];
            $addressObject->province = $data['address']['province'];
            $addressObject->country = $data['address']['country'] ?? 'UNITED STATES OF AMERICA';
            $addressObject->countryISOCode = $data['address']['countryISOCode'] ?? 'USA';
            $addressObject->uspsStreetAddressAbbreviation = $data['address']['streetAddressAbbreviation'];
            $addressObject->uspsUrbanization = $data['address']['urbanization'];
            $addressObject->uspsDeliveryPoint = $data['additionalInfo']['deliveryPoint'];
            $addressObject->uspsCarrierRoute = $data['additionalInfo']['carrierRoute'];
            $addressObject->uspsDpvConfirmation = $data['additionalInfo']['DPVConfirmation'];
            $addressObject->uspsDpvCmra = $data['additionalInfo']['DPVCMRA'] ?? null;
            $addressObject->uspsCentralDeliveryPoint = $data['additionalInfo']['centralDeliveryPoint'];
            $addressObject->uspsVacant = $data['additionalInfo']['vacant'];
            $addressObject->uspsBusiness = $data['additionalInfo']['business'];
            $addressObject->verifiedAt = date_create();
            $addressObject->_extra = $data;
        }

        return $addressObject->populate();
    }

    public function getAccessToken(): ?string
    {
        return $this->accessToken;
    }

    public function getAccessTokenExpiresAt(): ?int
    {
        return $this->accessTokenExpiresAt;
    }

    public function isRateLimitExceeded(): bool
    {
        return $this->isRateLimitExceeded;
    }

    private function isExpired(int $msTimestamp): bool
    {
        return (app_getTimestampInMilliseconds() > $msTimestamp);
    }
}
