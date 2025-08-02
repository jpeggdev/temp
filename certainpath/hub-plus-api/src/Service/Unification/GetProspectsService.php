<?php

namespace App\Service\Unification;

use App\Client\UnificationClient;
use App\DTO\Request\StochasticProspectQueryDTO;
use App\DTO\Response\StochasticProspectResponseDTO;
use App\Exception\APICommunicationException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

readonly class GetProspectsService
{
    public function __construct(
        private UnificationClient $unificationClient,
    ) {
    }

    /**
     * @throws APICommunicationException
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function getProspects(StochasticProspectQueryDTO $queryDto, string $intacctId): array
    {
        $url = $this->prepareUrl();
        $query = $this->prepareQuery($queryDto, $intacctId);

        try {
            $response = $this->unificationClient->sendGetRequest($url, $query);
            $this->validateResponse($response);

            $responseData = $response->toArray();
            $prospectsData = $responseData['data'] ?? [];
            $totalCount = $responseData['meta']['total'] ?? null;

            $prospects = array_map(
                static fn ($prospectData) => StochasticProspectResponseDTO::fromArray($prospectData),
                $prospectsData
            );

            return [
                'prospects' => $prospects,
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
            '%s/api/prospects',
            $this->unificationClient->getBaseUri(),
        );
    }

    private function prepareQuery(
        StochasticProspectQueryDTO $queryDto,
        string $intacctId,
    ): array {
        return [
            'searchTerm' => $queryDto->searchTerm,
            'intacctId' => $intacctId,
            'page' => $queryDto->page,
            'pageSize' => $queryDto->pageSize,
            'sortBy' => $queryDto->sortBy,
            'sortOrder' => strtoupper($queryDto->sortOrder),
        ];
    }

    /**
     * @throws TransportExceptionInterface
     */
    private function validateResponse(ResponseInterface $response): void
    {
        if (Response::HTTP_OK !== $response->getStatusCode()) {
            throw new \RuntimeException('Failed to retrieve prospects');
        }
    }
}
