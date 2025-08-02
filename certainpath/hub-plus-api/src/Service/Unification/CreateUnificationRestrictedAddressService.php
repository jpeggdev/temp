<?php

namespace App\Service\Unification;

use App\Client\UnificationClient;
use App\DTO\Request\RestrictedAddressCreateUpdateDTO;
use App\DTO\Response\RestrictedAddressResponseDTO;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\ResponseInterface;

readonly class CreateUnificationRestrictedAddressService
{
    public function __construct(private UnificationClient $unificationClient)
    {
    }

    public function createRestrictedAddress(RestrictedAddressCreateUpdateDTO $dto): RestrictedAddressResponseDTO
    {
        $url = $this->prepareUrl();
        $payload = [
            'address1' => $dto->address1,
            'address2' => $dto->address2,
            'city' => $dto->city,
            'stateCode' => $dto->stateCode,
            'postalCode' => $dto->postalCode,
            'countryCode' => $dto->countryCode,
        ];

        $response = $this->unificationClient->sendPostRequest($url, $payload);
        $this->validateResponse($response);

        $data = $response->toArray();

        return RestrictedAddressResponseDTO::fromArray($data['data']);
    }

    private function prepareUrl(): string
    {
        return sprintf('%s/api/restrictedAddress/create', $this->unificationClient->getBaseUri());
    }

    private function validateResponse(ResponseInterface $response): void
    {
        if (Response::HTTP_OK !== $response->getStatusCode()) {
            throw new \RuntimeException('Failed to create restricted address');
        }
    }
}
