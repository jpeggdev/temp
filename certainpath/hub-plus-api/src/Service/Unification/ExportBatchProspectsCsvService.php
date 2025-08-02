<?php

namespace App\Service\Unification;

use App\Client\UnificationClient;
use App\Exception\APICommunicationException;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

readonly class ExportBatchProspectsCsvService
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
    public function exportBatchProspectsCsv(int $batchId): void
    {
        $url = $this->prepareUrl($batchId);
        $headers = [
            'Accept' => 'text/csv',
        ];

        try {
            $this->unificationClient->sendGetRequestAndStream($url, headers: $headers);
        } catch (TransportExceptionInterface $e) {
            $message = 'Error communicating with Unification API: '.$e->getMessage();
            throw new APICommunicationException($message, $e);
        }
    }

    private function prepareUrl(int $batchId): string
    {
        return sprintf(
            '%s/api/batch/%d/prospects/export/csv',
            $this->unificationClient->getBaseUri(),
            $batchId
        );
    }
}
