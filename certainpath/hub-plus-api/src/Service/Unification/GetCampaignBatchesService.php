<?php

namespace App\Service\Unification;

use App\Client\UnificationClient;
use App\DTO\Response\BatchResponseDTO;
use App\Exception\APICommunicationException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

readonly class GetCampaignBatchesService
{
    public function __construct(
        private UnificationClient $unificationClient,
    ) {
    }

    /**
     * @throws APICommunicationException
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function getCampaignBatches(
        int $campaignId,
        int $page = 1,
        int $perPage = 10,
        string $sortOrder = 'DESC',
        ?int $batchStatusId = null,
    ): array {
        $url = $this->prepareUrl($campaignId);
        $query = $this->prepareQuery($page, $perPage, $sortOrder, $batchStatusId);

        try {
            $response = $this->unificationClient->sendGetRequest($url, $query);
            $this->validateResponse($response, $campaignId);

            $responseData = $response->toArray();
            $batchesData = $responseData['data'] ?? [];
            $totalCount = $responseData['meta']['total'] ?? null;

            if (empty($batchesData)) {
                return [
                    'batches' => [],
                    'totalCount' => $totalCount,
                ];
            }

            $batches = array_map(
                static fn ($batchData) => BatchResponseDTO::fromArray($batchData),
                $batchesData
            );

            return [
                'batches' => $batches,
                'totalCount' => $totalCount,
            ];
        } catch (TransportExceptionInterface $e) {
            $message = 'Error communicating with Unification API: '.$e->getMessage();
            throw new APICommunicationException($message, $e);
        } catch (\Exception $e) {
            $message = 'An unexpected error occurred while fetching batches:';
            throw new \RuntimeException($message, 0, $e);
        }
    }

    private function prepareUrl(int $campaignId): string
    {
        return sprintf(
            '%s/api/campaign/%d/batches',
            $this->unificationClient->getBaseUri(),
            $campaignId
        );
    }

    private function prepareQuery(
        int $page,
        int $perPage,
        string $sortOrder,
        ?int $batchStatusId = null,
    ): array {
        return [
            'page' => $page,
            'perPage' => $perPage,
            'sortOrder' => $sortOrder,
            'batchStatusId' => $batchStatusId,
        ];
    }

    /**
     * @throws TransportExceptionInterface
     */
    private function validateResponse(
        ResponseInterface $response,
        int $campaignId,
    ): void {
        $message = sprintf('Failed to retrieve batches for campaign ID %d', $campaignId);

        if (Response::HTTP_OK !== $response->getStatusCode()) {
            throw new \RuntimeException($message);
        }
    }
}
