<?php

namespace App\Module\Stochastic\Feature\DashboardPage\Service;

use App\Client\UnificationClient;
use App\Exception\APICommunicationException;
use App\Module\Stochastic\Feature\DashboardPage\DTO\Query\GetCityFilterOptionsDTO;
use App\Module\Stochastic\Feature\DashboardPage\Exception\FailedToGetCityFilterOptionsException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

readonly class GetCityFilterOptionsService
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
     * @throws FailedToGetCityFilterOptionsException
     */
    public function getFilterOptions(
        string $intacctId,
        GetCityFilterOptionsDTO $chartDTO,
    ): array {
        $url = $this->getUrl();
        $query = $this->prepareQuery($intacctId, $chartDTO);

        try {
            $response = $this->unificationClient->sendGetRequest($url, $query);

            return $this->validateResponse($response);
        } catch (TransportExceptionInterface $e) {
            $message = 'Error communicating with Unification API: '.$e->getMessage();
            throw new APICommunicationException($message, $e);
        }
    }

    private function getUrl(): string
    {
        return sprintf(
            '%s/api/filter-option/cities',
            $this->unificationClient->getBaseUri()
        );
    }

    private function prepareQuery(
        string $intacctId,
        GetCityFilterOptionsDTO $chartDTO,
    ): array {
        return [
            'page' => $chartDTO->page,
            'perSize' => $chartDTO->pageSize,
            'sortBy' => $chartDTO->sortBy,
            'sortOrder' => $chartDTO->sortOrder,
            'intacctId' => $intacctId,
            'searchTerm' => $chartDTO->searchTerm,
        ];
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     * @throws FailedToGetCityFilterOptionsException
     */
    private function validateResponse(ResponseInterface $response): array
    {
        if (Response::HTTP_OK !== $response->getStatusCode()) {
            throw new FailedToGetCityFilterOptionsException();
        }

        $responseData = $response->toArray();

        return $responseData['data'] ?? [];
    }
}
