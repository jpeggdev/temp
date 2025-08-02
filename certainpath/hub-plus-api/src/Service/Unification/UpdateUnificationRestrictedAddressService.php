<?php

namespace App\Service\Unification;

use App\Client\UnificationClient;
use App\DTO\Request\RestrictedAddressCreateUpdateDTO;
use App\DTO\Response\RestrictedAddressResponseDTO;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\ResponseInterface;

readonly class UpdateUnificationRestrictedAddressService
{
    public function __construct(private UnificationClient $unificationClient)
    {
    }

    public function updateRestrictedAddress(
        int $id,
        RestrictedAddressCreateUpdateDTO $dto,
    ): RestrictedAddressResponseDTO {
        $url = $this->prepareUrl($id);
        $payload = [
            'address1' => $dto->address1,
            'address2' => $dto->address2,
            'city' => $dto->city,
            'stateCode' => $dto->stateCode,
            'postalCode' => $dto->postalCode,
            'countryCode' => $dto->countryCode,
        ];

        $response = $this->unificationClient->sendPutRequest($url, $payload);
        $this->validateResponse($response);

        $data = $response->toArray();

        return RestrictedAddressResponseDTO::fromArray($data['data']);
    }

    private function prepareUrl(int $id): string
    {
        return sprintf(
            '%s/api/restrictedAddress/%d/edit',
            $this->unificationClient->getBaseUri(),
            $id
        );
    }

    private function validateResponse(ResponseInterface $response): void
    {
        if (Response::HTTP_OK !== $response->getStatusCode()) {
            throw new \RuntimeException('Failed to update restricted address');
        }
    }
}
