<?php

namespace App\Module\Stochastic\Feature\CampaignManagement\Service;

use App\Client\UnificationClient;
use App\DTO\Response\GetCampaignDetailsMetadataResponse;
use App\Exception\APICommunicationException;
use App\Exception\CampaignDetailsMetadataNotFoundException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

readonly class GetCampaignDetailsMetadataService
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
     * @throws CampaignDetailsMetadataNotFoundException
     */
    public function getDetailsMetadata(): GetCampaignDetailsMetadataResponse
    {
        $url = $this->prepareUrl();

        try {
            $response = $this->unificationClient->sendGetRequest($url);
            $campaignData = $this->validateResponse($response);

            return GetCampaignDetailsMetadataResponse::fromArray($campaignData);
        } catch (TransportExceptionInterface $e) {
            $message = 'Error communicating with Unification API: '.$e->getMessage();
            throw new APICommunicationException($message, $e);
        }
    }

    private function prepareUrl(): string
    {
        return sprintf(
            '%s/api/campaign-details-metadata',
            $this->unificationClient->getBaseUri()
        );
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface|CampaignDetailsMetadataNotFoundException
     */
    private function validateResponse(
        ResponseInterface $response,
    ): array {
        if (Response::HTTP_OK !== $response->getStatusCode()) {
            throw new CampaignDetailsMetadataNotFoundException();
        }

        $responseData = $response->toArray();
        $campaignDetailsMetadata = $responseData['data'] ?? [];

        if (empty($campaignDetailsMetadata)) {
            throw new CampaignDetailsMetadataNotFoundException();
        }

        return $campaignDetailsMetadata;
    }
}
