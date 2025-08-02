<?php

namespace App\Service\Unification;

use App\Client\UnificationClient;
use App\DTO\Request\GetCompanyProspectsAggregatedRequestDTO;
use App\DTO\Response\GetCompanyProspectsAggregatedResponseDTO;
use App\Exception\AggregatedProspectsRetrievalException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

readonly class GetCompanyProspectsAggregatedService
{
    public function __construct(
        private UnificationClient $unificationClient,
    ) {
    }

    /**
     * Calls the Unification API endpoint:
     *   GET /api/company/{identifier}/aggregated-prospects
     *
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     * @throws TransportExceptionInterface
     * @throws AggregatedProspectsRetrievalException
     */
    public function getAggregatedProspects(
        string $identifier,
        GetCompanyProspectsAggregatedRequestDTO $requestDTO,
    ): array {
        $url = $this->prepareUrl();
        $query = $this->prepareQuery($requestDTO, $identifier);

        $response = $this->unificationClient->sendGetRequest($url, $query);
        $this->validateResponse($response);
        $responseData = $response->toArray();

        return array_map(
            static fn (array $row) => GetCompanyProspectsAggregatedResponseDTO::fromArray($row),
            $responseData['data'] ?? []
        );
    }

    private function prepareUrl(): string
    {
        // ?XDEBUG_SESSION_START=PHPSTORM
        return sprintf(
            '%s/api/company/aggregated-prospects',
            $this->unificationClient->getBaseUri()
        );
    }

    /**
     * Build the query parameters from GetCompanyProspectsAggregatedRequestDTO.
     * Adjust key names if the Unification API expects different param names.
     */
    private function prepareQuery(GetCompanyProspectsAggregatedRequestDTO $dto, string $identifier): array
    {
        return [
            'intacctId' => $identifier,
            'customerInclusionRule' => $dto->customerInclusionRule,
            'lifetimeValueRule' => $dto->lifetimeValueRule,
            'clubMembersRule' => $dto->clubMembersRule,
            'installationsRule' => $dto->installationsRule,
            'prospectMinAge' => $dto->prospectMinAgeRule,
            'prospectMaxAge' => $dto->prospectMaxAgeRule,
            'minEstimatedIncome' => $dto->minEstimatedIncomeRule,
            'minHomeAge' => $dto->minHomeAgeRule,
            'tagsRule' => $dto->tagsRule,
            'addressTypeRule' => $dto->addressTypeRule,
            'locations' => $dto->locations,
        ];
    }

    /**
     * Ensures we only proceed if the response status is 200 (OK).
     * Throw more specific exceptions if needed (e.g., 404, 500, etc.).
     *
     * @throws AggregatedProspectsRetrievalException|TransportExceptionInterface
     */
    private function validateResponse(ResponseInterface $response): void
    {
        if (Response::HTTP_OK !== $response->getStatusCode()) {
            throw new AggregatedProspectsRetrievalException();
        }
    }
}
