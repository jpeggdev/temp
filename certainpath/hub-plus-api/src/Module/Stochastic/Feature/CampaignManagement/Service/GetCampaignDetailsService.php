<?php

namespace App\Module\Stochastic\Feature\CampaignManagement\Service;

use App\Client\UnificationClient;
use App\Exception\APICommunicationException;
use App\Exception\NotFoundException\CampaignNotFoundException;
use App\Repository\CampaignProductRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

readonly class GetCampaignDetailsService
{
    public function __construct(
        private UnificationClient $unificationClient,
        private CampaignProductRepository $campaignProductRepository,
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
    public function getDetails(int $campaignId): array
    {
        $url = $this->prepareUrl($campaignId);

        try {
            $response = $this->unificationClient->sendGetRequest($url);
            $campaignData = $this->validateResponse($response, $campaignId);
            $campaignData['campaignProduct'] = $this->prepareCampaignProductData($campaignData);

            return $campaignData;
        } catch (TransportExceptionInterface $e) {
            $message = 'Error communicating with Unification API: '.$e->getMessage();
            throw new APICommunicationException($message, $e);
        }
    }

    private function prepareUrl(int $campaignId): string
    {
        return sprintf(
            '%s/api/campaign/%d/details',
            $this->unificationClient->getBaseUri(),
            $campaignId
        );
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

    private function prepareCampaignProductData(array $campaignData): array
    {
        $campaignProductId = $campaignData['hubPlusProductId'] ?? null;

        if (!$campaignProductId) {
            return [];
        }

        $campaignProduct = $this->campaignProductRepository->findOneById($campaignProductId);

        return $campaignProduct
            ? [
                'id' => $campaignProduct->getId(),
                'name' => $campaignProduct->getName(),
            ]
            : [];
    }
}
