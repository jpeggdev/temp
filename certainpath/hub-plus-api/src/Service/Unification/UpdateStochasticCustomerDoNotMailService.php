<?php

namespace App\Service\Unification;

use App\Client\UnificationClient;
use App\DTO\Request\Customer\UpdateStochasticCustomerDoNotMailRequestDTO;
use App\DTO\Request\Prospect\UpdateStochasticProspectDoNotMailRequestDTO;
use App\Exception\APICommunicationException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

readonly class UpdateStochasticCustomerDoNotMailService
{
    public function __construct(
        private UnificationClient $unificationClient,
        private UpdateStochasticProspectDoNotMailServiceInterface $updateProspectService,
    ) {
    }

    /**
     * @throws APICommunicationException
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function updateCustomerDoNotMail(
        int $customerId,
        UpdateStochasticCustomerDoNotMailRequestDTO $dto,
        string $intacctId,
    ): array {
        $prospect = $this->getProspectByCustomerId($customerId, $intacctId);
        $prospectId = $prospect['id'];

        $prospectDTO = new UpdateStochasticProspectDoNotMailRequestDTO(
            $dto->doNotMail,
        );

        return $this->updateProspectService->updateProspectDoNotMail($prospectId, $prospectDTO);
    }

    /**
     * @throws APICommunicationException
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    private function getProspectByCustomerId(int $customerId, string $intacctId): array
    {
        $url = $this->prepareProspectLookupUrl($customerId);
        $payload = ['intacctId' => $intacctId];

        try {
            $response = $this->unificationClient->sendPatchRequest($url, $payload);

            if (Response::HTTP_NOT_FOUND === $response->getStatusCode()) {
                throw new \RuntimeException("No prospect found for customer {$customerId}");
            }

            $this->validateResponse($response);

            $responseData = $response->toArray();

            return $responseData['data'] ?? $responseData;
        } catch (TransportExceptionInterface $e) {
            $message = "Error communicating with Unification API: {$e->getMessage()}";
            throw new APICommunicationException($message, $e);
        }
    }

    private function prepareProspectLookupUrl(int $customerId): string
    {
        return sprintf(
            '%s/api/customers/%d/prospect',
            $this->unificationClient->getBaseUri(),
            $customerId
        );
    }

    /**
     * @throws TransportExceptionInterface
     */
    private function validateResponse(ResponseInterface $response): void
    {
        if (Response::HTTP_OK !== $response->getStatusCode()) {
            throw new \RuntimeException('Failed to update customer do-not-mail status');
        }
    }
}
