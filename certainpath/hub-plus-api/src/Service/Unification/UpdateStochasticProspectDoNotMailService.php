<?php

declare(strict_types=1);

namespace App\Service\Unification;

use App\Client\UnificationClient;
use App\DTO\Request\Prospect\UpdateStochasticProspectDoNotMailRequestDTO;
use App\Exception\APICommunicationException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

readonly class UpdateStochasticProspectDoNotMailService implements UpdateStochasticProspectDoNotMailServiceInterface
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
    public function updateProspectDoNotMail(
        int $prospectId,
        UpdateStochasticProspectDoNotMailRequestDTO $dto,
    ): array {
        try {
            $url = $this->prepareUpdateUrl($prospectId);
            $payload = ['doNotMail' => $dto->doNotMail];

            $response = $this->unificationClient->sendPatchRequest($url, $payload);
            $this->validateResponse($response);

            $preferredAddressUrl = $this->preparePreferredAddressUpdateUrl($prospectId);
            $preferredAddressResponse = $this->unificationClient->sendPatchRequest($preferredAddressUrl, $payload);
            $this->validateResponse($preferredAddressResponse);

            return $response->toArray();
        } catch (TransportExceptionInterface $e) {
            $message = "Error communicating with Unification API: {$e->getMessage()}";
            throw new APICommunicationException($message, $e);
        }
    }

    private function prepareUpdateUrl(int $prospectId): string
    {
        return sprintf(
            '%s/api/prospects/%d/do-not-mail',
            $this->unificationClient->getBaseUri(),
            $prospectId
        );
    }

    private function preparePreferredAddressUpdateUrl(int $prospectId): string
    {
        return sprintf(
            '%s/api/prospects/%d/preferred-address/do-not-mail',
            $this->unificationClient->getBaseUri(),
            $prospectId
        );
    }

    /**
     * @throws TransportExceptionInterface
     */
    private function validateResponse(ResponseInterface $response): void
    {
        if (Response::HTTP_OK !== $response->getStatusCode()) {
            throw new \RuntimeException('Failed to update prospect do-not-mail status');
        }
    }
}
