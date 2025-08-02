<?php

namespace App\Service\Unification;

use App\Client\UnificationClient;
use App\DTO\Response\BatchProspectResponseDTO;
use App\Exception\APICommunicationException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

readonly class GetBatchProspectsService
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
    public function getBatchProspects(
        int $batchId,
        int $page = 1,
        int $perPage = 10,
        string $sortOrder = 'DESC',
    ): array {
        $url = $this->prepareUrl($batchId);
        $query = $this->prepareQuery($page, $perPage, $sortOrder);

        try {
            $response = $this->unificationClient->sendGetRequest($url, $query);
            $this->validateResponse($response, $batchId);

            $responseData = $response->toArray();
            $prospectsData = $responseData['data'] ?? [];
            $totalCount = $responseData['meta']['total'] ?? null;

            if (empty($prospectsData)) {
                return [
                    'prospects' => [],
                    'totalCount' => $totalCount,
                ];
            }

            $prospects = array_map(
                static fn ($prospectData) => BatchProspectResponseDTO::fromArray($prospectData),
                $prospectsData
            );

            return [
                'prospects' => $prospects,
                'totalCount' => $totalCount,
            ];
        } catch (TransportExceptionInterface $e) {
            $message = 'Error communicating with Unification API: '.$e->getMessage();
            throw new APICommunicationException($message, $e);
        } catch (\Exception $e) {
            $message = 'An unexpected error occurred while fetching prospects';
            throw new \RuntimeException($message, 0, $e);
        }
    }

    private function prepareUrl(int $batchId): string
    {
        return sprintf(
            '%s/api/batch/%d/prospects',
            $this->unificationClient->getBaseUri(),
            $batchId
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
     * @throws TransportExceptionInterface
     */
    private function validateResponse(
        ResponseInterface $response,
        int $batchId,
    ): void {
        $message = sprintf('Failed to retrieve prospects for batch ID %d', $batchId);

        if (Response::HTTP_OK !== $response->getStatusCode()) {
            throw new \RuntimeException($message);
        }
    }
}
