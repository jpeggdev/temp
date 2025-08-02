<?php

namespace App\Service\Unification;

use App\Client\UnificationClient;
use App\DTO\Response\CampaignStatusResponseDTO;
use App\Exception\APICommunicationException;
use App\Exception\CampaignStatusesNotFoundException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

readonly class GetCampaignStatusesService
{
    public function __construct(
        private UnificationClient $unificationClient,
    ) {
    }

    /**
     * @throws APICommunicationException
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     * @throws ServerExceptionInterface
     * @throws CampaignStatusesNotFoundException
     */
    public function getCampaignStatuses(
        int $page = 1,
        int $perPage = 10,
        string $sortOrder = 'DESC',
    ): array {
        $url = $this->prepareUrl();
        $query = $this->prepareQuery($page, $perPage, $sortOrder);

        try {
            $response = $this->unificationClient->sendGetRequest($url, $query);
            $campaignStatusesData = $this->validateResponse($response);
            $campaignStatuses = array_map(
                static fn ($statusData) => CampaignStatusResponseDTO::fromArray($statusData),
                $campaignStatusesData
            );

            return [
                'campaignStatuses' => $campaignStatuses,
            ];
        } catch (TransportExceptionInterface $e) {
            $message = 'Error communicating with Unification API: '.$e->getMessage();
            throw new APICommunicationException($message, $e);
        }
    }

    private function prepareUrl(): string
    {
        return sprintf(
            '%s/api/campaign-statuses',
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
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws CampaignStatusesNotFoundException
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    private function validateResponse(ResponseInterface $response): array
    {
        if (Response::HTTP_OK !== $response->getStatusCode()) {
            throw new CampaignStatusesNotFoundException();
        }

        $responseData = $response->toArray();
        $campaignStatusesData = $responseData['data'] ?? [];

        if (empty($campaignStatusesData)) {
            throw new CampaignStatusesNotFoundException();
        }

        return $campaignStatusesData;
    }
}
