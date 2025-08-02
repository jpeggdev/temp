<?php

namespace App\Service\Unification\Location;

use App\Client\UnificationClient;
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

readonly class GetLocationService
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
    public function getLocation(int $id): LocationResponseDTO
    {
        $url = $this->prepareUrl($id);

        try {
            $response = $this->unificationClient->sendGetRequest($url);
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
