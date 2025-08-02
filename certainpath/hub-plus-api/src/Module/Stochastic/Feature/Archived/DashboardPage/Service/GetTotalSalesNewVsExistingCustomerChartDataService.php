<?php

namespace App\Module\Stochastic\Feature\Archived\DashboardPage\Service;

use App\Client\UnificationClient;
use App\Exception\APICommunicationException;
use App\Module\Stochastic\Feature\Archived\DashboardPage\Exception\FailedToGetTotalSalesNewVsExistingCustomerChartDataException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

readonly class GetTotalSalesNewVsExistingCustomerChartDataService
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
     * @throws FailedToGetTotalSalesNewVsExistingCustomerChartDataException
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
            '%s/api/chart/total-sales-new-vs-existing-customer',
            $this->unificationClient->getBaseUri(),
        );
    }

    public function prepareQuery(string $companyIntacctId): array
    {
        return [
            'intacctId' => $companyIntacctId,
            'sortOrder' => 'ASC',
        ];
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     * @throws FailedToGetTotalSalesNewVsExistingCustomerChartDataException
     */
    private function validateResponse(ResponseInterface $response): array
    {
        if (Response::HTTP_OK !== $response->getStatusCode()) {
            throw new FailedToGetTotalSalesNewVsExistingCustomerChartDataException();
        }

        $responseData = $response->toArray();

        return $responseData['data'] ?? [];
    }
}
