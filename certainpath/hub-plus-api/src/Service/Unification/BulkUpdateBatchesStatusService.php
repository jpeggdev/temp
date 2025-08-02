<?php

declare(strict_types=1);

namespace App\Service\Unification;

use App\Client\UnificationClient;
use App\Exception\BatchArchiveException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

readonly class BulkUpdateBatchesStatusService
{
    public function __construct(
        private UnificationClient $unificationClient,
    ) {
    }

    /**
     * @throws BatchArchiveException
     * @throws ClientExceptionInterface
     * @throws ServerExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws TransportExceptionInterface
     * @throws RedirectionExceptionInterface
     */
    public function bulkUpdateStatus(
        int $year,
        int $week,
        string $status,
    ): void {
        $url = $this->prepareUrl();
        $payload = $this->preparePayload($year, $week, $status);
        $response = $this->unificationClient->sendPatchRequest($url, $payload);
        $this->validateResponse($response);
    }

    private function prepareUrl(): string
    {
        // ?XDEBUG_SESSION_START=PHPSTORM
        return sprintf(
            '%s/api/batches/bulk-update-status',
            $this->unificationClient->getBaseUri(),
        );
    }

    private function preparePayload(int $year, int $week, string $status): array
    {
        return [
            'year' => $year,
            'week' => $week,
            'status' => $status,
        ];
    }

    /**
     * @throws BatchArchiveException
     * @throws ServerExceptionInterface
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws TransportExceptionInterface
     * @throws RedirectionExceptionInterface
     */
    private function validateResponse(ResponseInterface $response): void
    {
        if (Response::HTTP_OK !== $response->getStatusCode()) {
            $responseArray = $response->toArray(false);
            $errorMessage = $responseArray['errors']['detail'] ?? 'Unknown error bulk updating batches status.';

            throw new BatchArchiveException($errorMessage);
        }
    }
}
