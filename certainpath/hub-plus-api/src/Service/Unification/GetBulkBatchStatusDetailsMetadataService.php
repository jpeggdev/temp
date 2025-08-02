<?php

declare(strict_types=1);

namespace App\Service\Unification;

use App\Client\UnificationClient;
use App\DTO\Response\GetBulkBatchStatusDetailsMetadataResponse;
use App\Exception\APICommunicationException;
use App\Exception\BulkBatchStatusDetailsMetadataNotFoundException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

readonly class GetBulkBatchStatusDetailsMetadataService
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
     * @throws BulkBatchStatusDetailsMetadataNotFoundException
     */
    public function getDetailsMetadata(
        int $year,
        int $week,
    ): GetBulkBatchStatusDetailsMetadataResponse {
        $url = $this->prepareUrl();
        $payload = $this->preparePayload($year, $week);

        try {
            $response = $this->unificationClient->sendGetRequest($url, $payload);
            $bulkBatchesStatusData = $this->validateResponse($response);

            return GetBulkBatchStatusDetailsMetadataResponse::fromArray($bulkBatchesStatusData);
        } catch (TransportExceptionInterface $e) {
            $message = 'Error communicating with Unification API: '.$e->getMessage();
            throw new APICommunicationException($message, $e);
        }
    }

    private function prepareUrl(): string
    {
        return sprintf(
            '%s/api/details-metadata/batch/bulk-update-status',
            $this->unificationClient->getBaseUri(),
        );
    }

    private function preparePayload(int $year, int $week): array
    {
        return [
            'year' => $year,
            'week' => $week,
        ];
    }

    /**
     * @throws ClientExceptionInterface
     * @throws ServerExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws TransportExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws BulkBatchStatusDetailsMetadataNotFoundException
     */
    private function validateResponse(
        ResponseInterface $response,
    ): array {
        if (Response::HTTP_OK !== $response->getStatusCode()) {
            throw new BulkBatchStatusDetailsMetadataNotFoundException();
        }

        $responseData = $response->toArray();
        $bulkBatchStatusDetailsMetadata = $responseData['data'] ?? [];

        if (empty($bulkBatchStatusDetailsMetadata)) {
            throw new BulkBatchStatusDetailsMetadataNotFoundException();
        }

        return $bulkBatchStatusDetailsMetadata;
    }
}
