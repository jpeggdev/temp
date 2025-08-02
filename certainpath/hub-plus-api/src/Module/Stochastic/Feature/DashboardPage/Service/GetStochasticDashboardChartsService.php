<?php

namespace App\Module\Stochastic\Feature\DashboardPage\Service;

use App\Client\UnificationClient;
use App\Exception\APICommunicationException;
use App\Module\Stochastic\Feature\DashboardPage\DTO\Query\GetStochasticDashboardDTO;
use App\Module\Stochastic\Feature\DashboardPage\Exception\FailedToGetStochasticDashboardDataException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

readonly class GetStochasticDashboardChartsService
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
     * @throws FailedToGetStochasticDashboardDataException
     */
    public function getData(
        string $intacctId,
        GetStochasticDashboardDTO $queryDTO,
    ): array {
        $url = $this->getUrl();
        $query = $this->prepareQuery($intacctId, $queryDTO);

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
            '%s/api/stochastic/dashboard',
            $this->unificationClient->getBaseUri()
        );
    }

    private function prepareQuery(
        string $intacctId,
        GetStochasticDashboardDTO $chartDTO,
    ): array {
        return [
            'intacctId' => $intacctId,
            'scope' => $chartDTO->scope,
            'years' => $chartDTO->years,
            'trades' => $chartDTO->trades,
            'cities' => $chartDTO->cities,
        ];
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     * @throws FailedToGetStochasticDashboardDataException
     */
    private function validateResponse(ResponseInterface $response): array
    {
        if (Response::HTTP_OK !== $response->getStatusCode()) {
            throw new FailedToGetStochasticDashboardDataException();
        }

        $responseData = $response->toArray();

        return $responseData['data'] ?? [];
    }
}
