<?php

namespace App\Service;

use App\Client\UnificationClient;
use App\DTO\Request\TagQueryDTO;
use App\DTO\Response\CompanyTagsResponseDTO;
use App\Exception\TagsRetrievalException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

readonly class GetUnificationTagsService
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
     * @throws TagsRetrievalException
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     */
    public function getTags(
        TagQueryDTO $tagQueryDTO,
    ): CompanyTagsResponseDTO {
        $url = $this->prepareUrl($tagQueryDTO->companyIdentifier);
        $response = $this->unificationClient->sendGetRequest(
            $url,
            [
                'searchTerm' => $tagQueryDTO->searchTerm,
                'systemTags' => $tagQueryDTO->systemTags,
            ]
        );

        $this->validateResponse($response);
        $responseData = $response->toArray();

        return CompanyTagsResponseDTO::fromArray(
            $responseData['data']
        );
    }

    private function prepareUrl(string $companyIdentifier): string
    {
        return sprintf(
            '%s/api/company/%s/tags',
            $this->unificationClient->getBaseUri(),
            $companyIdentifier
        );
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ClientExceptionInterface
     * @throws TagsRetrievalException
     */
    private function validateResponse(
        ResponseInterface $response,
    ): void {
        $statusCode = $response->getStatusCode();
        if (Response::HTTP_OK !== $statusCode) {
            $message = sprintf(
                'Tags retrieval failed with status code %d',
                $statusCode
            );
            throw new TagsRetrievalException($message);
        }
    }
}
