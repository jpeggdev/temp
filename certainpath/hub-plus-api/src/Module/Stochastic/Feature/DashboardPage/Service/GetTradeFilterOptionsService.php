<?php

namespace App\Module\Stochastic\Feature\DashboardPage\Service;

use App\Client\UnificationClient;
use App\Exception\APICommunicationException;
use App\Module\Stochastic\Feature\DashboardPage\DTO\Query\GetTradeFilterOptionsDTO;
use App\Module\Stochastic\Feature\DashboardPage\Exception\FailedToGetTradeFilterOptionsException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

readonly class GetTradeFilterOptionsService
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
     * @throws FailedToGetTradeFilterOptionsException
     */
    public function getFilterOptions(GetTradeFilterOptionsDTO $chartDTO): array
    {
        $url = $this->getUrl();
        $query = $this->prepareQuery($chartDTO);

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
            '%s/api/filter-option/trades',
            $this->unificationClient->getBaseUri()
        );
    }

    private function prepareQuery(
        GetTradeFilterOptionsDTO $chartDTO,
    ): array {
        return [
            'page' => $chartDTO->page,
            'perSize' => $chartDTO->pageSize,
            'sortBy' => $chartDTO->sortBy,
            'sortOrder' => $chartDTO->sortOrder,
            'searchTerm' => $chartDTO->searchTerm,
        ];
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     * @throws FailedToGetTradeFilterOptionsException
     */
    private function validateResponse(ResponseInterface $response): array
    {
        if (Response::HTTP_OK !== $response->getStatusCode()) {
            throw new FailedToGetTradeFilterOptionsException();
        }

        $responseData = $response->toArray();

        return $responseData['data'] ?? [];
    }
}
