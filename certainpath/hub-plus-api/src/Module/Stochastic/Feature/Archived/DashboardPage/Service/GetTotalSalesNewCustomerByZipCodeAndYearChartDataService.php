<?php

namespace App\Module\Stochastic\Feature\Archived\DashboardPage\Service;

use App\Client\UnificationClient;
use App\Exception\APICommunicationException;
use App\Module\Stochastic\Feature\Archived\DashboardPage\DTO\Query\GetTotalSalesNewCustomerByZipCodeAndYearChartDTO;
use App\Module\Stochastic\Feature\Archived\DashboardPage\Exception\FailedToGetTotalSalesNewCustomerByZipCodeAndYearChartDataException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

readonly class GetTotalSalesNewCustomerByZipCodeAndYearChartDataService
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
     * @throws FailedToGetTotalSalesNewCustomerByZipCodeAndYearChartDataException
     */
    public function getChartData(
        string $companyIntacctId,
        GetTotalSalesNewCustomerByZipCodeAndYearChartDTO $chartDTO = new GetTotalSalesNewCustomerByZipCodeAndYearChartDTO(),
    ): array {
        $url = $this->getUrl();
        $query = $this->prepareQuery($companyIntacctId, $chartDTO);

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
            '%s/api/chart/total-sales-new-customer-by-zip-code-and-year',
            $this->unificationClient->getBaseUri()
        );
    }

    private function prepareQuery(
        string $companyIntacctId,
        GetTotalSalesNewCustomerByZipCodeAndYearChartDTO $chartDTO,
    ): array {
        return [
            'intacctId' => $companyIntacctId,
            'trades' => $chartDTO->trades,
            'years' => $chartDTO->years,
            'cities' => $chartDTO->cities,
        ];
    }

    /**
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     * @throws FailedToGetTotalSalesNewCustomerByZipCodeAndYearChartDataException
     */
    private function validateResponse(ResponseInterface $response): array
    {
        if (Response::HTTP_OK !== $response->getStatusCode()) {
            throw new FailedToGetTotalSalesNewCustomerByZipCodeAndYearChartDataException();
        }

        $responseData = $response->toArray();

        return $responseData['data'] ?? [];
    }
}
