<?php

namespace App\Service\Unification\Location;

use App\Client\UnificationClient;
use App\DTO\Query\Location\LocationDTO;
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

readonly class GetLocationsService
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
    public function getLocations(
        LocationDTO $queryDTO,
        string $intacctId,
    ): array {
        $url = $this->prepareUrl();
        $query = $this->prepareQuery($queryDTO, $intacctId);

        try {
            $response = $this->unificationClient->sendGetRequest($url, $query);
            $this->validateResponse($response);

            $responseData = $response->toArray();
            $locationsData = $responseData['data'] ?? [];
            $totalCount = $responseData['meta']['total'] ?? null;

            $locations = array_map(
                static fn ($locationData) => LocationResponseDTO::fromArray($locationData),
                $locationsData
            );

            return [
                'locations' => $locations,
                'totalCount' => $totalCount,
            ];
        } catch (TransportExceptionInterface $e) {
            $message = 'Error communicating with Unification API: '.$e->getMessage();
            throw new APICommunicationException($message, $e);
        }
    }

    private function prepareUrl(): string
    {
        return sprintf(
            '%s/api/locations',
            $this->unificationClient->getBaseUri(),
        );
    }

    private function prepareQuery(
        LocationDTO $queryDTO,
        string $inacctId,
    ): array {
        return [
            'searchTerm' => $queryDTO->searchTerm,
            'page' => $queryDTO->page,
            'pageSize' => $queryDTO->pageSize,
            'sortBy' => $queryDTO->sortBy,
            'sortOrder' => strtoupper($queryDTO->sortOrder),
            'companyIdentifier' => $inacctId,
            'isActive' => $queryDTO->isActive,
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
