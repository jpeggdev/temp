<?php

namespace App\Service\Unification;

use App\Client\UnificationClient;
use App\Exception\APICommunicationException;
use App\Module\Stochastic\Feature\CampaignManagement\DTO\Response\CampaignResponseDTO;
use App\Module\Stochastic\Feature\CampaignManagement\Service\CampaignService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

readonly class GetCompanyCampaignsService
{
    public function __construct(
        private UnificationClient $unificationClient,
        private CampaignService $campaignService,
    ) {
    }

    /**
     * @throws APICommunicationException
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function getCompanyCampaigns(
        string $companyIntacctId,
        int $page = 1,
        int $perPage = 10,
        string $sortOrder = 'DESC',
        ?int $campaignStatusId = null,
    ): array {
        $url = $this->prepareUrl($companyIntacctId);
        $query = $this->prepareQuery($page, $perPage, $sortOrder, $campaignStatusId);

        try {
            $response = $this->unificationClient->sendGetRequest($url, $query);
            $this->validateResponse($response);

            $responseData = $response->toArray();
            $campaignsData = $responseData['data'] ?? [];
            $totalCount = $responseData['meta']['total'] ?? null;

            if (empty($campaignsData)) {
                return [
                    'campaigns' => [],
                    'totalCount' => $totalCount,
                ];
            }

            $campaigns = [];
            foreach ($campaignsData as $campaignData) {
                $campaigns[] = $this->campaignService->hydrateCampaignResponseDTO(
                    CampaignResponseDTO::fromArray($campaignData),
                );
            }

            return [
                'campaigns' => $campaigns,
                'totalCount' => $totalCount,
            ];
        } catch (TransportExceptionInterface $e) {
            $message = 'Error communicating with Unification API: '.$e->getMessage();
            throw new APICommunicationException($message, $e);
        } catch (\Exception $e) {
            $message = 'An unexpected error occurred while fetching campaigns.';
            throw new \RuntimeException($message, 0, $e);
        }
    }

    private function prepareUrl(string $companyIntacctId): string
    {
        return sprintf(
            '%s/api/company/%s/campaigns',
            $this->unificationClient->getBaseUri(),
            $companyIntacctId
        );
    }

    private function prepareQuery(
        int $page,
        int $perPage,
        string $sortOrder,
        ?int $campaignStatusId = null,
    ): array {
        return [
            'page' => $page,
            'perPage' => $perPage,
            'sortOrder' => $sortOrder,
            'campaignStatusId' => $campaignStatusId,
            'includes' => ['campaignStatus'],
        ];
    }

    /**
     * @throws TransportExceptionInterface
     */
    private function validateResponse(ResponseInterface $response): void
    {
        if (Response::HTTP_OK !== $response->getStatusCode()) {
            throw new \RuntimeException('Failed to retrieve campaigns');
        }
    }
}
