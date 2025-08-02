<?php

namespace App\Module\Stochastic\Feature\CampaignManagement\Service;

use App\Client\UnificationClient;
use App\DTO\Request\UpdateCampaignDTO;
use App\Exception\APICommunicationException;
use App\Exception\CampaignUpdateException;
use App\Module\Stochastic\Feature\CampaignManagement\DTO\Response\CampaignResponseDTO;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

readonly class UpdateCampaignService
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
     * @throws CampaignUpdateException
     */
    public function updateCampaign(
        int $campaignId,
        UpdateCampaignDTO $updateCampaignDTO,
    ): CampaignResponseDTO {
        $url = $this->prepareUrl($campaignId);
        $payload = $this->preparePayload($updateCampaignDTO);

        try {
            $response = $this->unificationClient->sendPatchRequest($url, $payload);
            $campaignData = $this->validateResponse($response, $campaignId);

            return CampaignResponseDTO::fromArray($campaignData);
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

    private function preparePayload(UpdateCampaignDTO $updateCampaignDTO): array
    {
        $fieldsToUpdate = [
            'name' => $updateCampaignDTO->name,
            'description' => $updateCampaignDTO->description,
            'phoneNumber' => $updateCampaignDTO->phoneNumber,
            'status' => $updateCampaignDTO->status,
        ];

        return array_filter($fieldsToUpdate, static fn ($value) => !is_null($value));
    }

    /**
     * @throws CampaignUpdateException
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
            throw new CampaignUpdateException("Failed to update campaign {$campaignId}. Status code: {$response->getStatusCode()}");
        }

        $responseData = $response->toArray();
        $campaignData = $responseData['data'] ?? [];

        if (empty($campaignData)) {
            throw new CampaignUpdateException("Failed to update campaign {$campaignId}. Updated campaign data is missing in the response.");
        }

        return $campaignData;
    }
}
