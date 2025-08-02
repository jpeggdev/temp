<?php

namespace App\Service\Unification;

use App\Client\UnificationClient;
use App\DTO\Response\BulkBatchStatusEventResponseDTO;
use App\Exception\APICommunicationException;
use App\Exception\BulkBatchStatusEventNotFoundException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

readonly class GetBulkBatchStatusEventsService
{
    public function __construct(
        private UnificationClient $unificationClient,
    ) {
    }

    /**
     * @throws ServerExceptionInterface
     * @throws ClientExceptionInterface
     * @throws APICommunicationException
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws BulkBatchStatusEventNotFoundException
     */
    public function getBulkBatchStatusEvents(
        int $year = 2025,
        int $week = 1,
        string $sortOrder = 'DESC',
    ): array {
        $url = $this->prepareUrl();
        $query = $this->prepareQuery($year, $week, $sortOrder);

        try {
            $response = $this->unificationClient->sendGetRequest($url, $query);
            $bulkBatchStatusEventsData = $this->validateResponse($response);
            $bulkBatchStatusEvents = array_map(
                static fn ($statusData) => BulkBatchStatusEventResponseDTO::fromArray($statusData),
                $bulkBatchStatusEventsData
            );

            return [
                'bulkBatchStatusEvents' => $bulkBatchStatusEvents,
            ];
        } catch (TransportExceptionInterface $e) {
            $message = 'Error communicating with Unification API: '.$e->getMessage();
            throw new APICommunicationException($message, $e);
        }
    }

    private function prepareUrl(): string
    {
        // ?XDEBUG_SESSION_START=PHPSTORM
        return sprintf(
            '%s/api/bulk-batch-status-events',
            $this->unificationClient->getBaseUri(),
        );
    }

    private function prepareQuery(
        int $year,
        int $week,
        string $sortOrder,
    ): array {
        return [
            'year' => $year,
            'week' => $week,
            'sortOrder' => $sortOrder,
        ];
    }

    /**
     * @throws ServerExceptionInterface
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws TransportExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws BulkBatchStatusEventNotFoundException
     */
    private function validateResponse(ResponseInterface $response): array
    {
        if (Response::HTTP_OK !== $response->getStatusCode()) {
            throw new BulkBatchStatusEventNotFoundException();
        }

        $responseData = $response->toArray();

        return $responseData['data'] ?? [];
    }
}
