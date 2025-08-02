<?php

declare(strict_types=1);

namespace App\Module\Stochastic\Feature\CampaignManagement\Service;

use App\Client\UnificationClient;
use App\Exception\CampaignPauseException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

readonly class PauseCampaignService
{
    public function __construct(
        private UnificationClient $unificationClient,
    ) {
    }

    /**
     * @throws CampaignPauseException
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function pauseCampaign(int $campaignId): void
    {
        $url = $this->prepareUrl($campaignId);
        $response = $this->unificationClient->sendPatchRequest($url, []);
        $this->validateResponse($response);
    }

    private function prepareUrl(int $campaignId): string
    {
        // ?XDEBUG_SESSION_START=PHPSTORM
        return sprintf(
            '%s/api/campaign/pause/%d',
            $this->unificationClient->getBaseUri(),
            $campaignId
        );
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     * @throws CampaignPauseException
     */
    private function validateResponse(ResponseInterface $response): void
    {
        if (Response::HTTP_OK !== $response->getStatusCode()) {
            $responseArray = $response->toArray(false);
            $errorMessage = $responseArray['errors']['detail'] ?? 'Unknown error pausing campaign.';
            throw new CampaignPauseException($errorMessage);
        }
    }
}
