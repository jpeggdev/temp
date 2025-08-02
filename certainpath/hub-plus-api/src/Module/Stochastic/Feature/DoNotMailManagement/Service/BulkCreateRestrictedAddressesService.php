<?php

namespace App\Module\Stochastic\Feature\DoNotMailManagement\Service;

use App\Client\UnificationClient;
use App\Module\Stochastic\Feature\DoNotMailManagement\DTO\BulkCreateRestrictedAddressesDTO;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

readonly class BulkCreateRestrictedAddressesService
{
    public function __construct(
        private UnificationClient $unificationClient,
    ) {
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function bulkCreate(BulkCreateRestrictedAddressesDTO $dto): array
    {
        $url = $this->prepareUrl();
        $payload = $this->preparePayload($dto);

        $response = $this->unificationClient->sendPostRequest($url, $payload);
        $this->validateResponse($response);

        return [
            'message' => 'The addresses have been added to the do not mail list successfully',
        ];
    }

    private function prepareUrl(): string
    {
        return sprintf('%s/api/restricted-address/bulk-create', $this->unificationClient->getBaseUri());
    }

    private function preparePayload(BulkCreateRestrictedAddressesDTO $dto): array
    {
        return [
            'addresses' => $dto->addresses,
        ];
    }

    /**
     * @throws TransportExceptionInterface
     */
    private function validateResponse(ResponseInterface $response): void
    {
        if (Response::HTTP_OK !== $response->getStatusCode()) {
            throw new \RuntimeException('Failed to bulk create restricted addresses.');
        }
    }
}
