<?php

namespace App\Module\Stochastic\Feature\Archived\DashboardPage\Service;

use App\Client\UnificationClient;
use App\Exception\APICommunicationException;
use App\Module\Stochastic\Feature\Archived\DashboardPage\DTO\Query\GetTotalSalesByZipCodeChartDTO;
use App\Module\Stochastic\Feature\Archived\DashboardPage\Exception\FailedToGetTotalSalesByZipCodeChartDataException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

readonly class GetTotalSalesByZipCodeChartDataService
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
     * @throws FailedToGetTotalSalesByZipCodeChartDataException
     */
    public function getChartData(
        string $companyIntacctId,
        GetTotalSalesByZipCodeChartDTO $chartDTO = new GetTotalSalesByZipCodeChartDTO(),
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
            '%s/api/chart/total-sales-by-zip-code',
            $this->unificationClient->getBaseUri()
        );
    }

    private function prepareQuery(
        string $companyIntacctId,
        GetTotalSalesByZipCodeChartDTO $chartDTO,
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
     * @throws FailedToGetTotalSalesByZipCodeChartDataException
     */
    private function validateResponse(ResponseInterface $response): array
    {
        if (Response::HTTP_OK !== $response->getStatusCode()) {
            throw new FailedToGetTotalSalesByZipCodeChartDataException();
        }

        $responseData = $response->toArray();

        return $responseData['data'] ?? [];
    }
}
