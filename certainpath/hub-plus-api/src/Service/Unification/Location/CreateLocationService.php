<?php

namespace App\Service\Unification\Location;

use App\Client\UnificationClient;
use App\DTO\Request\Location\CreateUpdateLocationDTO;
use App\DTO\Response\Unification\Location\LocationResponseDTO;
use App\Exception\Unification\LocationCreationException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

readonly class CreateLocationService
{
    public function __construct(
        private UnificationClient $unificationClient,
    ) {
    }

    /**
     * @throws LocationCreationException
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function createLocation(
        CreateUpdateLocationDTO $createLocationDTO,
        string $intacctId,
    ): LocationResponseDTO {
        $url = $this->prepareUrl();
        $payload = $this->preparePayload($createLocationDTO, $intacctId);
        $response = $this->unificationClient->sendPostRequest($url, $payload);
        $responseData = $this->validateResponse($response);

        return LocationResponseDTO::fromArray($responseData);
    }

    private function prepareUrl(): string
    {
        // ?XDEBUG_SESSION_START=PHPSTORM
        return sprintf(
            '%s/api/location/create',
            $this->unificationClient->getBaseUri()
        );
    }

    private function preparePayload(
        CreateUpdateLocationDTO $createLocationDTO,
        string $intacctId,
    ): array {
        return [
            'name' => $createLocationDTO->name,
            'description' => $createLocationDTO->description,
            'companyIdentifier' => $intacctId,
            'postalCodes' => $createLocationDTO->postalCodes,
        ];
    }

    /**
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws LocationCreationException
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    private function validateResponse(ResponseInterface $response): array
    {
        if (Response::HTTP_OK !== $response->getStatusCode()) {
            $responseArray = $response->toArray(false);
            $errorMessage = $responseArray['errors']['detail'] ?? 'Unknown error creating location';
            throw new LocationCreationException($errorMessage);
        }

        $responseData = $response->toArray();

        return $responseData['data'] ?? [];
    }
}
