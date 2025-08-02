<?php

namespace App\Module\Stochastic\Feature\Archived\DashboardPage\Service;

use App\Client\UnificationClient;
use App\Exception\APICommunicationException;
use App\Module\Stochastic\Feature\Archived\DashboardPage\Exception\FailedToGetTotalSalesByYearAndMonthChartDataException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

readonly class GetTotalSalesByYearAndMonthChartDataService
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
     * @throws FailedToGetTotalSalesByYearAndMonthChartDataException
     */
    public function getChartData(string $companyIntacctId): array
    {
        $url = $this->prepareUrl();
        $query = $this->prepareQuery($companyIntacctId);

        try {
            $response = $this->unificationClient->sendGetRequest($url, $query);

            return $this->validateResponse($response);
        } catch (TransportExceptionInterface $e) {
            $message = 'Error communicating with Unification API: '.$e->getMessage();
            throw new APICommunicationException($message, $e);
        }
    }

    private function prepareUrl(): string
    {
        return sprintf(
            '%s/api/chart/total-sales-by-year-and-month',
            $this->unificationClient->getBaseUri(),
        );
    }

    private function prepareQuery(string $companyIntacctId): array
    {
        return [
            'intacctId' => $companyIntacctId,
        ];
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     * @throws FailedToGetTotalSalesByYearAndMonthChartDataException
     */
    private function validateResponse(ResponseInterface $response): array
    {
        if (Response::HTTP_OK !== $response->getStatusCode()) {
            throw new FailedToGetTotalSalesByYearAndMonthChartDataException();
        }

        $responseData = $response->toArray();

        return $responseData['data'] ?? [];
    }
}
