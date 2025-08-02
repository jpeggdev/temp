<?php

namespace App\Client;

use Symfony\Component\Mime\Part\DataPart;
use Symfony\Component\Mime\Part\Multipart\FormDataPart;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

abstract class DomainClient
{
    protected HttpClientInterface $httpClient;
    protected string $baseUri;
    protected string $apiKey;

    public function __construct(
        HttpClientInterface $httpClient,
        string $domainApiBaseUrl,
        string $apiKey,
    ) {
        $this->httpClient = $httpClient;
        $this->baseUri = rtrim($domainApiBaseUrl, '/');
        $this->apiKey = $apiKey;
    }

    protected function getAuthorizationHeader(): array
    {
        return [
            'X-API-Key' => $this->apiKey,
        ];
    }

    public function getBaseUri(): string
    {
        return $this->baseUri;
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function sendGetRequest(
        string $url,
        array $query = [],
        array $headers = [],
    ): ResponseInterface {
        return $this->httpClient->request('GET', $url, [
            'headers' => array_merge(
                $headers,
                $this->getAuthorizationHeader(),
            ),
            'query' => $query,
        ]);
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function sendGetRequestAndStream(
        string $url,
        array $query = [],
        array $headers = [],
    ): void {
        $response = $this->sendGetRequest($url, $query, $headers);
        $stream = $this->httpClient->stream($response);

        foreach ($stream as $chunk) {
            echo $chunk->getContent();
            flush();
        }
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function sendPostRequest(
        string $url,
        array $payload,
        array $headers = [],
    ): ResponseInterface {
        return $this->httpClient->request('POST', $url, [
            'headers' => array_merge(
                $headers,
                $this->getAuthorizationHeader(),
            ),
            'json' => $payload,
        ]);
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function sendPostMultipartRequest(
        string $url,
        array $files = [],
        array $fields = [],
        array $headers = [],
    ): ResponseInterface {
        $formData = array_map(static function ($uploadedFile) {
            return DataPart::fromPath(
                $uploadedFile->getPathname(),
                $uploadedFile->getClientOriginalName()
            );
        }, $files);

        foreach ($fields as $name => $value) {
            $formData[$name] = $value;
        }

        $form = new FormDataPart($formData);

        return $this->httpClient->request('POST', $url, [
            'headers' => array_merge(
                $headers,
                $this->getAuthorizationHeader(),
                $form->getPreparedHeaders()->toArray()
            ),
            'body' => $form->bodyToIterable(),
        ]);
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function sendPatchRequest(
        string $url,
        array $payload,
    ): ResponseInterface {
        return $this->httpClient->request('PATCH', $url, [
            'headers' => [
                'X-API-Key' => $this->apiKey,
            ],
            'json' => $payload,
        ]);
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function sendPutRequest(
        string $url,
        array $payload,
    ): ResponseInterface {
        return $this->httpClient->request('PUT', $url, [
            'headers' => [
                'X-API-Key' => $this->apiKey,
            ],
            'json' => $payload,
        ]);
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function sendDeleteRequest(
        string $url,
    ): ResponseInterface {
        return $this->httpClient->request('DELETE', $url, [
            'headers' => [
                'X-API-Key' => $this->apiKey,
            ],
        ]);
    }
}
