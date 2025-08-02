<?php

namespace App\Module\Stochastic\Feature\DashboardPage\Service;

use App\Client\UnificationClient;
use App\Exception\APICommunicationException;
use App\Module\Stochastic\Feature\DashboardPage\DTO\Query\GetYearFilterOptionsDTO;
use App\Module\Stochastic\Feature\DashboardPage\Exception\FailedToGetYearFilterOptionsException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

readonly class GetYearFilterOptionsService
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
     * @throws FailedToGetYearFilterOptionsException
     */
    public function getFilterOptions(
        string $intacctId,
        GetYearFilterOptionsDTO $chartDTO,
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
            '%s/api/filter-option/years',
            $this->unificationClient->getBaseUri()
        );
    }

    private function prepareQuery(
        string $intacctId,
        GetYearFilterOptionsDTO $chartDTO,
    ): array {
        return [
            'intacctId' => $intacctId,
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
     * @throws FailedToGetYearFilterOptionsException
     */
    private function validateResponse(ResponseInterface $response): array
    {
        if (Response::HTTP_OK !== $response->getStatusCode()) {
            throw new FailedToGetYearFilterOptionsException();
        }

        $responseData = $response->toArray();

        return $responseData['data'] ?? [];
    }
}
