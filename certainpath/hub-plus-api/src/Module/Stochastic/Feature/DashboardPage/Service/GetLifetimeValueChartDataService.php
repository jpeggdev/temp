<?php

namespace App\Module\Stochastic\Feature\DashboardPage\Service;

use App\Client\UnificationClient;
use App\Exception\APICommunicationException;
use App\Module\Stochastic\Feature\Archived\DashboardPage\DTO\Query\GetLifetimeValueChartDTO;
use App\Module\Stochastic\Feature\Archived\DashboardPage\Exception\FailedToGetLifetimeValueChartDataException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

readonly class GetLifetimeValueChartDataService
{
    public function __construct(
        private UnificationClient $unificationClient,
    ) {
    }

    /**
     * @throws APICommunicationException
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws FailedToGetLifetimeValueChartDataException
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     */
    public function getChartData(
        string $companyIntacctId,
        GetLifetimeValueChartDTO $chartDTO = new GetLifetimeValueChartDTO(),
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
            '%s/api/chart/lifetime-value',
            $this->unificationClient->getBaseUri()
        );
    }

    private function prepareQuery(
        string $companyIntacctId,
        GetLifetimeValueChartDTO $chartDTO,
    ): array {
        return [
            'intacctId' => $companyIntacctId,
            'trades' => $chartDTO->trades,
            'years' => $chartDTO->years,
            'cities' => $chartDTO->cities,
        ];
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     * @throws FailedToGetLifetimeValueChartDataException
     */
    private function validateResponse(ResponseInterface $response): array
    {
        if (Response::HTTP_OK !== $response->getStatusCode()) {
            throw new FailedToGetLifetimeValueChartDataException();
        }

        $responseData = $response->toArray();

        return $responseData['data'] ?? [];
    }
}
