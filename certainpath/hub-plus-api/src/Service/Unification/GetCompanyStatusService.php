<?php

namespace App\Service\Unification;

use App\Client\UnificationClient;
use App\DTO\Response\GetCompanyStatusResponseDTO;
use App\Exception\CompanyStatusRetrievalException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

readonly class GetCompanyStatusService
{
    public function __construct(
        private UnificationClient $unificationClient,
    ) {
    }

    /**
     * Calls the Unification API endpoint:
     *    GET /api/company/{identifier}/status
     *
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws CompanyStatusRetrievalException
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     */
    public function getCompanyStatus(
        string $companyIdentifier,
    ): GetCompanyStatusResponseDTO {
        $url = $this->prepareUrl($companyIdentifier);
        $response = $this->unificationClient->sendGetRequest(
            $url
        );

        $this->validateResponse($response);
        $responseData = $response->toArray();

        return GetCompanyStatusResponseDTO::fromArray(
            $responseData['data']
        );
    }

    private function prepareUrl(string $companyIdentifier): string
    {
        return sprintf(
            '%s/api/company/%s/status',
            $this->unificationClient->getBaseUri(),
            $companyIdentifier
        );
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ClientExceptionInterface
     * @throws CompanyStatusRetrievalException
     */
    private function validateResponse(
        ResponseInterface $response,
    ): void {
        $statusCode = $response->getStatusCode();
        if (Response::HTTP_OK !== $statusCode) {
            $message = sprintf(
                'Company status retrieval failed with status code %d',
                $statusCode
            );
            throw new CompanyStatusRetrievalException($message);
        }
    }
}
