<?php

namespace App\Service\Unification;

use App\Client\UnificationClient;
use App\DTO\Request\RestrictedAddressQueryDTO;
use App\DTO\Response\RestrictedAddressResponseDTO;
use App\Exception\APICommunicationException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

readonly class GetUnificationRestrictedAddressesService
{
    public function __construct(private UnificationClient $unificationClient)
    {
    }

    public function getRestrictedAddresses(
        RestrictedAddressQueryDTO $queryDto,
    ): array {
        $url = $this->prepareUrl();
        $query = $this->prepareQuery($queryDto);

        try {
            $response = $this->unificationClient->sendGetRequest($url, $query);
            $this->validateResponse($response);

            $responseData = $response->toArray();
            $items = $responseData['data'] ?? [];
            $meta = $responseData['meta'] ?? [];

            $addresses = array_map(
                fn (array $item) => RestrictedAddressResponseDTO::fromArray($item),
                $items
            );

            return [
                'addresses' => $addresses,
                'total' => $meta['total'] ?? 0,
            ];
        } catch (TransportExceptionInterface $e) {
            throw new APICommunicationException('Error communicating with Unification (getRestrictedAddresses): '.$e->getMessage(), $e);
        }
    }

    private function prepareUrl(): string
    {
        return sprintf('%s/api/restrictedAddresses', $this->unificationClient->getBaseUri());
    }

    private function prepareQuery(RestrictedAddressQueryDTO $queryDto): array
    {
        return [
            'externalId' => $queryDto->externalId,
            'address1' => $queryDto->address1,
            'address2' => $queryDto->address2,
            'city' => $queryDto->city,
            'stateCode' => $queryDto->stateCode,
            'postalCode' => $queryDto->postalCode,
            'countryCode' => $queryDto->countryCode,
            'isBusiness' => $queryDto->isBusiness,
            'isVacant' => $queryDto->isVacant,
            'isVerified' => $queryDto->isVerified,
            'sortOrder' => $queryDto->sortOrder,
            'sortBy' => $queryDto->sortBy,
            'page' => $queryDto->page,
            'perPage' => $queryDto->perPage,
        ];
    }

    private function validateResponse(ResponseInterface $response): void
    {
        if (Response::HTTP_OK !== $response->getStatusCode()) {
            throw new \RuntimeException('Failed to retrieve restricted addresses');
        }
    }
}
