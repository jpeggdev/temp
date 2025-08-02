<?php

namespace App\Service;

use App\Client\UnificationClient;
use App\Entity\Company;
use App\Exception\CompanyProcessDispatchException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

readonly class UnificationCompanyProcessingDispatchService
{
    public function __construct(
        private UnificationClient $unificationClient,
    ) {
    }

    /**
     * @throws TransportExceptionInterface
     * @throws CompanyProcessDispatchException
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function dispatchProcessingForCompany(
        Company $company,
    ): void {
        $url = $this->prepareUrl($company);
        $response = $this->unificationClient->sendGetRequest(
            $url
        );
        $this->validateResponse($response, $company);
    }

    private function prepareUrl(Company $company): string
    {
        return sprintf(
            '%s/api/company/process/%s',
            $this->unificationClient->getBaseUri(),
            $company->getIntacctId()
        );
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     * @throws CompanyProcessDispatchException
     */
    private function validateResponse(
        ResponseInterface $response,
        Company $company,
    ): void {
        if (Response::HTTP_OK !== $response->getStatusCode()) {
            $responseArray = $response->toArray(false);
            $errorMessage = $responseArray['errors']['detail']
            ??
            'Unknown error creating Dispatching Processing for Company: '
            .
            $company->getIntacctId()
            ;
            throw new CompanyProcessDispatchException($errorMessage);
        }
    }
}
