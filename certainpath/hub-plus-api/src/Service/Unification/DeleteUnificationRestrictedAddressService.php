<?php

namespace App\Service\Unification;

use App\Client\UnificationClient;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\ResponseInterface;

readonly class DeleteUnificationRestrictedAddressService
{
    public function __construct(private UnificationClient $unificationClient)
    {
    }

    public function deleteRestrictedAddress(int $id): void
    {
        $url = $this->prepareUrl($id);
        $response = $this->unificationClient->sendDeleteRequest($url);
        $this->validateResponse($response);
    }

    private function prepareUrl(int $id): string
    {
        return sprintf(
            '%s/api/restrictedAddress/%d/delete',
            $this->unificationClient->getBaseUri(),
            $id
        );
    }

    private function validateResponse(ResponseInterface $response): void
    {
        if (Response::HTTP_OK !== $response->getStatusCode()) {
            throw new \RuntimeException('Failed to delete restricted address');
        }
    }
}
