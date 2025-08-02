<?php

namespace App\Service\Unification;

use App\Client\UnificationClient;
use App\DTO\Response\RestrictedAddressResponseDTO;
use App\Exception\APICommunicationException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

readonly class GetUnificationRestrictedAddressService
{
    public function __construct(private UnificationClient $unificationClient)
    {
    }

    /**
     * @throws APICommunicationException
     */
    public function getRestrictedAddress(int $id): RestrictedAddressResponseDTO
    {
        $url = $this->prepareUrl($id);

        try {
            $response = $this->unificationClient->sendGetRequest($url);
            $this->validateResponse($response);

            $responseData = $response->toArray();
            $item = $responseData['data'] ?? null;
            if (!$item) {
                throw new \RuntimeException('Missing restricted address data from Unification');
            }

            return RestrictedAddressResponseDTO::fromArray($item);
        } catch (TransportExceptionInterface $e) {
            throw new APICommunicationException('Error communicating with Unification (getRestrictedAddress): '.$e->getMessage(), $e);
        }
    }

    private function prepareUrl(int $id): string
    {
        return sprintf('%s/api/restrictedAddress/%d', $this->unificationClient->getBaseUri(), $id);
    }

    private function validateResponse(ResponseInterface $response): void
    {
        if (Response::HTTP_OK !== $response->getStatusCode()) {
            throw new \RuntimeException('Failed to retrieve restricted address');
        }
    }
}
