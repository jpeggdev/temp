<?php

namespace App\Service\Unification;

use App\Client\UnificationClient;
use App\DTO\Request\CreateCampaignFileDTO;
use App\Exception\APICommunicationException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

readonly class CreateCampaignFileService
{
    public function __construct(
        private UnificationClient $unificationClient,
    ) {
    }

    /**
     * @throws TransportExceptionInterface
     * @throws APICommunicationException
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function createCampaignFile(
        int $campaignId,
        CreateCampaignFileDTO $fileDto,
    ): array {
        $url = $this->unificationClient->getBaseUri().'/api/campaign/'.$campaignId.'/file';
        $headers = [
            'Content-Type' => 'application/json',
        ];
        $payload = $this->preparePayload($fileDto);

        try {
            $response = $this->unificationClient->sendPostRequest($url, $headers, $payload);
            $this->validateResponse($response, $campaignId);
        } catch (TransportExceptionInterface $e) {
            $message = 'Error creating campaign file: '.$e->getMessage();
            throw new APICommunicationException($message, $e);
        }

        return $response->toArray();
    }

    private function preparePayload(CreateCampaignFileDTO $fileDto): array
    {
        return [
            'originalFilename' => $fileDto->originalFilename,
            'bucketName' => $fileDto->bucketName,
            'objectKey' => $fileDto->objectKey,
            'contentType' => $fileDto->contentType,
        ];
    }

    /**
     * @throws TransportExceptionInterface
     */
    private function validateResponse(
        ResponseInterface $response,
        int $campaignId,
    ): void {
        $message = "Failed to create campaign file for campaign ID {$campaignId}";

        if (Response::HTTP_OK !== $response->getStatusCode()) {
            throw new \RuntimeException($message);
        }
    }
}
