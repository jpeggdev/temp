<?php

namespace App\Service\Unification;

use App\Client\UnificationClient;
use App\DTO\Response\BatchStatusResponseDTO;
use App\Exception\APICommunicationException;
use App\Exception\BatchStatusesNotFoundException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

readonly class GetBatchStatusesService
{
    public function __construct(
        private UnificationClient $unificationClient,
    ) {
    }

    /**
     * @throws ClientExceptionInterface
     * @throws ServerExceptionInterface
     * @throws APICommunicationException
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws BatchStatusesNotFoundException
     */
    public function getBatchStatuses(
        int $page = 1,
        int $perPage = 10,
        string $sortOrder = 'DESC',
    ): array {
        $url = $this->prepareUrl();
        $query = $this->prepareQuery($page, $perPage, $sortOrder);

        try {
            $response = $this->unificationClient->sendGetRequest($url, $query);
            $batchStatusesData = $this->validateResponse($response);
            $campaignStatuses = array_map(
                static fn ($statusData) => BatchStatusResponseDTO::fromArray($statusData),
                $batchStatusesData
            );

            return [
                'batchStatuses' => $campaignStatuses,
            ];
        } catch (TransportExceptionInterface $e) {
            $message = 'Error communicating with Unification API: '.$e->getMessage();
            throw new APICommunicationException($message, $e);
        }
    }

    private function prepareUrl(): string
    {
        return sprintf(
            '%s/api/batch-statuses',
            $this->unificationClient->getBaseUri(),
        );
    }

    private function prepareQuery(
        int $page,
        int $perPage,
        string $sortOrder,
    ): array {
        return [
            'page' => $page,
            'perPage' => $perPage,
            'sortOrder' => $sortOrder,
        ];
    }

    /**
     * @throws ServerExceptionInterface
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws TransportExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws BatchStatusesNotFoundException
     */
    private function validateResponse(ResponseInterface $response): array
    {
        if (Response::HTTP_OK !== $response->getStatusCode()) {
            throw new BatchStatusesNotFoundException();
        }

        $responseData = $response->toArray();
        $batchStatusesData = $responseData['data'] ?? [];

        if (empty($batchStatusesData)) {
            throw new BatchStatusesNotFoundException();
        }

        return $batchStatusesData;
    }
}
