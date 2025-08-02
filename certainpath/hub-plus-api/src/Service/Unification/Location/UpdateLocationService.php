<?php

namespace App\Service\Unification\Location;

use App\Client\UnificationClient;
use App\DTO\Request\Location\CreateUpdateLocationDTO;
use App\DTO\Response\Unification\Location\LocationResponseDTO;
use App\Exception\APICommunicationException;
use App\Exception\Unification\NotFoundException\LocationNotFoundException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

readonly class UpdateLocationService
{
    public function __construct(
        private UnificationClient $unificationClient,
    ) {
    }

    /**
     * @throws APICommunicationException
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     */
    public function updateLocation(
        int $locationId,
        string $intacctId,
        CreateUpdateLocationDTO $updateCreateLocationDTO,
    ): LocationResponseDTO {
        $url = $this->prepareUrl($locationId);
        $payload = $this->preparePayload($updateCreateLocationDTO, $intacctId);

        try {
            $response = $this->unificationClient->sendPutRequest($url, $payload);
            $this->validateResponse($response);

            $responseData = $response->toArray();
            $locationData = $responseData['data'] ?? [];

            return LocationResponseDTO::fromArray($locationData);
        } catch (TransportExceptionInterface $e) {
            $message = 'Error communicating with Unification API: '.$e->getMessage();
            throw new APICommunicationException($message, $e);
        }
    }

    private function prepareUrl(int $locationId): string
    {
        return sprintf(
            '%s/api/location/%d',
            $this->unificationClient->getBaseUri(),
            $locationId
        );
    }

    private function preparePayload(
        CreateUpdateLocationDTO $updateLocationDTO,
        string $intacctId,
    ): array {
        return [
            'name' => $updateLocationDTO->name,
            'description' => $updateLocationDTO->description,
            'postalCodes' => $updateLocationDTO->postalCodes,
            'companyIdentifier' => $intacctId,
        ];
    }

    /**
     * @throws TransportExceptionInterface
     */
    private function validateResponse(ResponseInterface $response): void
    {
        if (Response::HTTP_OK !== $response->getStatusCode()) {
            throw new LocationNotFoundException();
        }
    }
}
