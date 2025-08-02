<?php

declare(strict_types=1);

namespace App\Service\Unification;

use App\Client\UnificationClient;
use App\DTO\Response\StochasticClientMailDataRowDTO;
use App\Exception\APICommunicationException;
use App\Module\Stochastic\Feature\CampaignManagement\Service\CampaignService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

readonly class GetStochasticClientMailDataService
{
    public function __construct(
        private UnificationClient $unificationClient,
        private CampaignService $campaignService,
    ) {
    }

    /**
     * @return array{
     *   mailDataRows: StochasticClientMailDataRowDTO[],
     *   totalCount: int|null,
     *   currentPage: int|null,
     *   perPage: int|null
     * }
     *
     * @throws APICommunicationException
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function getStochasticMailData(
        int $week = 1,
        int $year = 2025,
        int $page = 1,
        int $perPage = 10,
        string $sortOrder = 'DESC',
    ): array {
        $url = $this->prepareUrl();
        $queryParams = $this->prepareQuery($week, $year, $page, $perPage, $sortOrder);

        try {
            $response = $this->unificationClient->sendGetRequest($url, $queryParams);
            $this->validateResponse($response);
            $responseData = $response->toArray();
            $rowsData = $responseData['data'] ?? [];
            $meta = $responseData['meta'] ?? [];
            $totalCount = $meta['total'] ?? null;
            $currentPage = $meta['currentPage'] ?? null;
            $perPageVal = $meta['perPage'] ?? null;

            $mailDataRows = [];
            foreach ($rowsData as $row) {
                $mailDataRows[] = $this->campaignService->hydrateStochasticClientMailDataRowDTO(
                    StochasticClientMailDataRowDTO::fromArray($row)
                );
            }

            return [
                'mailDataRows' => $mailDataRows,
                'totalCount' => $totalCount,
                'currentPage' => $currentPage,
                'perPage' => $perPageVal,
            ];
        } catch (TransportExceptionInterface $e) {
            $message = 'Error communicating with Unification API: '.$e->getMessage();
            throw new APICommunicationException($message, $e);
        } catch (\Exception $e) {
            $message = 'An unexpected error occurred while fetching Stochastic client mail data.';
            throw new \RuntimeException($message, 0, $e);
        }
    }

    private function prepareUrl(): string
    {
        return sprintf(
            '%s/api/stochastic/client-mail-data',
            rtrim($this->unificationClient->getBaseUri(), '/')
        );
    }

    private function prepareQuery(
        int $week,
        int $year,
        int $page,
        int $perPage,
        string $sortOrder,
    ): array {
        return [
            'week' => $week,
            'year' => $year,
            'page' => $page,
            'perPage' => $perPage,
            'sortOrder' => $sortOrder,
        ];
    }

    /**
     * @throws \RuntimeException|TransportExceptionInterface
     */
    private function validateResponse(ResponseInterface $response): void
    {
        if (Response::HTTP_OK !== $response->getStatusCode()) {
            throw new \RuntimeException('Failed to retrieve Stochastic client mail data. Received HTTP Status '.$response->getStatusCode());
        }
    }
}
