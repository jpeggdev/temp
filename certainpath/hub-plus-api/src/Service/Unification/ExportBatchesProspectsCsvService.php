<?php

namespace App\Service\Unification;

use App\Client\UnificationClient;
use App\DTO\Query\Export\GetBatchesProspectsCsvExportDTO;
use App\Exception\APICommunicationException;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

readonly class ExportBatchesProspectsCsvService
{
    public function __construct(
        private UnificationClient $unificationClient,
    ) {
    }

    /**
     * @throws ClientExceptionInterface
     * @throws ServerExceptionInterface
     * @throws APICommunicationException
     * @throws RedirectionExceptionInterface
     */
    public function exportBatchesProspectsCsv(GetBatchesProspectsCsvExportDTO $dto): void
    {
        $url = $this->prepareUrl();
        $query = $this->prepareQuery($dto);
        $headers = $this->prepareHeaders();

        try {
            $this->unificationClient->sendGetRequestAndStream($url, $query, $headers);
        } catch (TransportExceptionInterface $e) {
            $message = 'Error communicating with Unification API: '.$e->getMessage();
            throw new APICommunicationException($message, $e);
        }
    }

    private function prepareUrl(): string
    {
        return sprintf(
            '%s/api/batches/prospects/export/csv',
            $this->unificationClient->getBaseUri(),
        );
    }

    private function prepareQuery(GetBatchesProspectsCsvExportDTO $dto): array
    {
        return [
            'week' => $dto->week,
            'year' => $dto->year,
        ];
    }

    private function prepareHeaders(): array
    {
        return [
            'Accept' => 'text/csv',
        ];
    }
}
