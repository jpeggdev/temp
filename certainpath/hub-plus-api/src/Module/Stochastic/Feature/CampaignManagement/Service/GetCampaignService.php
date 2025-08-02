<?php

namespace App\Module\Stochastic\Feature\CampaignManagement\Service;

use App\Client\UnificationClient;
use App\Exception\APICommunicationException;
use App\Exception\NotFoundException\CampaignNotFoundException;
use App\Module\Stochastic\Feature\CampaignManagement\DTO\Response\CampaignResponseDTO;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

readonly class GetCampaignService
{
    public function __construct(
        private UnificationClient $unificationClient,
        private CampaignService $campaignService,
    ) {
    }

    /**
     * @throws APICommunicationException
     * @throws CampaignNotFoundException
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     */
    public function getCampaign(int $campaignId): CampaignResponseDTO
    {
        $url = $this->prepareUrl($campaignId);
        $query = $this->prepareQuery();

        try {
            $response = $this->unificationClient->sendGetRequest($url, $query);
            $campaignData = $this->validateResponse($response, $campaignId);

            return $this->campaignService->hydrateCampaignResponseDTO(
                CampaignResponseDTO::fromArray($campaignData),
            );
        } catch (TransportExceptionInterface $e) {
            $message = 'Error communicating with Unification API: '.$e->getMessage();
            throw new APICommunicationException($message, $e);
        }
    }

    private function prepareUrl(int $campaignId): string
    {
        return sprintf(
            '%s/api/campaign/%d',
            $this->unificationClient->getBaseUri(),
            $campaignId
        );
    }

    private function prepareQuery(): array
    {
        return [
            'includes' => [
                'campaignStatus',
            ],
        ];
    }

    /**
     * @throws CampaignNotFoundException
     * @throws TransportExceptionInterface
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     */
    private function validateResponse(
        ResponseInterface $response,
        int $campaignId,
    ): array {
        if (Response::HTTP_OK !== $response->getStatusCode()) {
            throw new CampaignNotFoundException($campaignId);
        }

        $responseData = $response->toArray();
        $campaignData = $responseData['data'] ?? [];

        if (empty($campaignData)) {
            throw new CampaignNotFoundException($campaignId);
        }

        return $campaignData;
    }
}
