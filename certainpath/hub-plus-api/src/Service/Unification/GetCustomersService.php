<?php

namespace App\Service\Unification;

use App\Client\UnificationClient;
use App\DTO\Request\StochasticCustomerQueryDTO;
use App\DTO\Response\StochasticCustomerResponseDTO;
use App\Exception\APICommunicationException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

readonly class GetCustomersService
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
     */
    public function getCustomers(
        StochasticCustomerQueryDTO $queryDto,
        string $intacctId,
    ): array {
        $url = $this->prepareUrl();
        $query = $this->prepareQuery($queryDto, $intacctId);

        try {
            $response = $this->unificationClient->sendGetRequest($url, $query);
            $this->validateResponse($response);

            $responseData = $response->toArray();
            $customersData = $responseData['data'] ?? [];
            $totalCount = $responseData['meta']['total'] ?? null;

            $customers = array_map(
                static fn ($customerData) => StochasticCustomerResponseDTO::fromArray($customerData),
                $customersData
            );

            return [
                'customers' => $customers,
                'totalCount' => $totalCount,
            ];
        } catch (TransportExceptionInterface $e) {
            $message = 'Error communicating with Unification API: '.$e->getMessage();
            throw new APICommunicationException($message, $e);
        }
    }

    private function prepareUrl(): string
    {
        return sprintf(
            '%s/api/customers',
            $this->unificationClient->getBaseUri(),
        );
    }

    private function prepareQuery(
        StochasticCustomerQueryDTO $queryDto,
        string $intacctId,
    ): array {
        return [
            'searchTerm' => $queryDto->searchTerm,
            'intacctId' => $intacctId,
            'page' => $queryDto->page,
            'pageSize' => $queryDto->pageSize,
            'sortBy' => $queryDto->sortBy,
            'sortOrder' => strtoupper($queryDto->sortOrder),
            'isActive' => $queryDto->isActive,
        ];
    }

    /**
     * @throws TransportExceptionInterface
     */
    private function validateResponse(ResponseInterface $response): void
    {
        if (Response::HTTP_OK !== $response->getStatusCode()) {
            throw new \RuntimeException('Failed to retrieve customers');
        }
    }
}
