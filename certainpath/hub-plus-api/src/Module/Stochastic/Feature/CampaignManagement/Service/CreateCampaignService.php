<?php

namespace App\Module\Stochastic\Feature\CampaignManagement\Service;

use App\Client\UnificationClient;
use App\Exception\Unification\CampaignCreationException;
use App\Module\Stochastic\Feature\CampaignManagement\DTO\Request\CreateCampaignDTO;
use App\Module\Stochastic\Feature\CampaignManagement\DTO\Response\CampaignResponseDTO;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

readonly class CreateCampaignService
{
    public function __construct(
        private UnificationClient $unificationClient,
    ) {
    }

    /**
     * @throws CampaignCreationException
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function createCampaign(
        CreateCampaignDTO $createCampaignDTO,
        string $companyIntacctId,
    ): CampaignResponseDTO {
        $url = $this->prepareUrl();
        $payload = $this->preparePayload($createCampaignDTO, $companyIntacctId);
        $response = $this->unificationClient->sendPostRequest($url, $payload);
        $this->validateResponse($response);

        return CampaignResponseDTO::fromArrayAsync();
    }

    private function prepareUrl(): string
    {
        // ?XDEBUG_SESSION_START=PHPSTORM
        return sprintf(
            '%s/api/campaign/create-async',
            $this->unificationClient->getBaseUri()
        );
    }

    /**
     * Builds the payload that matches what the Unification API's CreateCampaignDTO expects.
     *
     * @param CreateCampaignDTO $createCampaignDTO The local DTO from your controller
     * @param string            $companyIntacctId  The current company's Intacct ID
     */
    private function preparePayload(
        CreateCampaignDTO $createCampaignDTO,
        string $companyIntacctId,
    ): array {
        $filterCriteria = $createCampaignDTO->filterCriteria;
        $prospectAge = $filterCriteria->prospectAge;

        $postalCodesMap = [];
        foreach ($createCampaignDTO->zipCodes as $zip) {
            $postalCodesMap[$zip->code] = (int) $zip->selectedProspects;
        }

        $tags = array_filter(array_map('trim', (!empty($createCampaignDTO->tags)) ?
            explode(',', $createCampaignDTO->tags) :
            []));

        return [
            'name' => $createCampaignDTO->campaignName,
            'hubPlusProductId' => $createCampaignDTO->campaignProduct,
            'startDate' => $createCampaignDTO->startDate,
            'endDate' => $createCampaignDTO->endDate,
            'mailingFrequencyWeeks' => $createCampaignDTO->mailingFrequency,
            'companyIdentifier' => $companyIntacctId,
            'mailPackageName' => $createCampaignDTO->campaignName, // TODO might need to change later. Use the campaign name for now
            'description' => $createCampaignDTO->description,
            'phoneNumber' => $createCampaignDTO->phoneNumber,
            'mailingDropWeeks' => $createCampaignDTO->selectedMailingWeeks,
            'locationIds' => $createCampaignDTO->locations,
            'prospectFilterRules' => [
                'intacctId' => $companyIntacctId,
                'customerInclusionRule' => $filterCriteria->audience,
                'addressTypeRule' => $filterCriteria->addressType,
                'lifetimeValueRule' => $filterCriteria->excludeLTV ? 5000 : null,
                'clubMembersRule' => $filterCriteria->excludeClubMembers ? 'exclude_club_members' : '',
                'installationsRule' => $filterCriteria->excludeInstallCustomers ? 'exclude_customer_installations' : '',
                'prospectMinAge' => (int) $prospectAge->min,
                'prospectMaxAge' => (int) $prospectAge->max,
                'minHomeAge' => $filterCriteria->homeAge ? (int) $filterCriteria->homeAge : null,
                'minEstimatedIncome' => $filterCriteria->estimatedIncome !== ''
                    ? (int) $filterCriteria->estimatedIncome
                    : null,
                'postalCodes' => $postalCodesMap,
                'tags' => $tags,
            ],
        ];
    }

    /**
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     * @throws CampaignCreationException
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     */
    private function validateResponse(ResponseInterface $response): array
    {
        if (Response::HTTP_OK !== $response->getStatusCode()) {
            $responseArray = $response->toArray(false);
            $errorMessage = $responseArray['errors']['detail'] ?? 'Unknown error creating campaign';
            throw new CampaignCreationException($errorMessage);
        }

        // todo talk to Leo about the empty response async, etc
        $responseData = $response->toArray();

        return $responseData['data'] ?? [];
    }
}
