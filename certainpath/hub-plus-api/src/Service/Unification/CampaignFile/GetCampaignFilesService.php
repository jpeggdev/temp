<?php

namespace App\Service\Unification\CampaignFile;

use App\Client\UnificationClient;
use App\DTO\Query\CampaignFile\CampaignFileQueryDTO;
use App\DTO\Response\CampaignFileListResponseDTO;
use App\Exception\APICommunicationException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

readonly class GetCampaignFilesService
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
    public function getCampaignFiles(int $campaignId, CampaignFileQueryDTO $queryDto): array
    {
        $url = $this->prepareUrl($campaignId);
        $query = $this->prepareQuery($queryDto);

        try {
            $response = $this->unificationClient->sendGetRequest($url, $query);
            $this->validateResponse($response, $campaignId);

            $responseData = $response->toArray();
            $filesData = $responseData['data'] ?? [];
            $totalCount = $responseData['meta']['total'] ?? null;

            $files = array_map(
                static fn ($fileData) => CampaignFileListResponseDTO::fromArray($fileData),
                $filesData
            );

            return [
                'files' => $files,
                'totalCount' => $totalCount,
            ];
        } catch (TransportExceptionInterface $e) {
            $message = 'Error communicating with Unification API: '.$e->getMessage();
            throw new APICommunicationException($message, $e);
        }
    }

    private function prepareUrl(int $campaignId): string
    {
        return sprintf(
            '%s/api/campaign/%d/files',
            $this->unificationClient->getBaseUri(),
            $campaignId
        );
    }

    private function prepareQuery(CampaignFileQueryDTO $queryDto): array
    {
        return [
            'searchTerm' => $queryDto->searchTerm,
            'page' => $queryDto->page,
            'pageSize' => $queryDto->pageSize,
            'sortBy' => $queryDto->sortBy,
            'sortOrder' => strtoupper($queryDto->sortOrder),
        ];
    }

    /**
     * @throws TransportExceptionInterface
     */
    private function validateResponse(
        ResponseInterface $response,
        int $campaignId,
    ): void {
        $message = sprintf('Failed to retrieve files for campaign ID %d', $campaignId);

        if (Response::HTTP_OK !== $response->getStatusCode()) {
            throw new \RuntimeException($message);
        }
    }
}
