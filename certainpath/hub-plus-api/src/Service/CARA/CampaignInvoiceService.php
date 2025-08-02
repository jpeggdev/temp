<?php

declare(strict_types=1);

namespace App\Service\CARA;

use App\Client\CaraClient;
use App\Client\UnificationClient;
use App\DTO\Request\CampaignInvoice\CampaignInvoiceDTO;
use App\Entity\BatchInvoice;
use App\Exception\CARA\CaraAPIException;
use App\Exception\UnificationAPIException;
use App\Repository\BatchInvoiceRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

readonly class CampaignInvoiceService
{
    public function __construct(
        private CaraClient $caraClient,
        private UnificationClient $unificationClient,
        private BatchInvoiceRepository $batchInvoiceRepository,
    ) {
    }

    public function createInvoices(array $data): array
    {
        $response = [];
        foreach ($data['invoices'] as $invoice) {
            $type = '';
            if (str_starts_with(strtolower($invoice['type']), CampaignInvoiceDTO::TYPE_LETTER)) {
                $type = CampaignInvoiceDTO::TYPE_LETTER;
            }
            if (str_starts_with(strtolower($invoice['type']), CampaignInvoiceDTO::TYPE_POSTCARD)) {
                $type = CampaignInvoiceDTO::TYPE_POSTCARD;
            }

            $campaignInvoiceDTO = new CampaignInvoiceDTO(
                $invoice['accountIdentifier'],
                $type,
                $invoice['quantityMailed'],
                $invoice['serviceUnitPrice'],
                $invoice['postageUnitPrice'],
                $invoice['batchReference'],
                null
            );

            try {
                $response[] = $this->createInvoice($campaignInvoiceDTO);
            } catch (CaraAPIException|UnificationAPIException) {
                $response[] = $campaignInvoiceDTO;
            }
        }

        return $response;
    }

    /**
     * @throws CaraAPIException
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     * @throws UnificationAPIException
     * @throws \JsonException
     */
    public function createInvoice(CampaignInvoiceDTO $campaignInvoiceDTO): CampaignInvoiceDTO
    {
        $caraUrl = $this->prepareCaraUrl();
        $caraPayload = $this->prepareCaraPayload($campaignInvoiceDTO);

        $uniUrl = $this->prepareUnificationUrl($campaignInvoiceDTO);
        $uniPayload = $this->prepareUnificationPayload('invoiced');

        $batchReference = (string) $campaignInvoiceDTO->batchReference;
        $batchInvoice = $this->batchInvoiceRepository->findOneByBatchReference(
            $batchReference
        ) ?? new BatchInvoice();

        if (!$batchInvoice->getId()) {
            $caraResponse = $this->caraClient->sendPostRequest($caraUrl, $caraPayload);
            $this->validateCaraResponse($caraResponse);
            $caraResponseArr = $caraResponse->toArray(false);
            $campaignInvoiceDTO->invoiceReference = $caraResponseArr['data']['identifier'] ?? null;

            if ($campaignInvoiceDTO->invoiceReference) {
                $batchInvoice->setAccountIdentifier($campaignInvoiceDTO->accountIdentifier);
                $batchInvoice->setInvoiceReference($campaignInvoiceDTO->invoiceReference);
                $batchInvoice->setBatchReference($batchReference);
                $batchInvoice->setData(json_encode($campaignInvoiceDTO, JSON_THROW_ON_ERROR));
                $batchInvoice = $this->batchInvoiceRepository->saveBatchInvoice($batchInvoice);
            }
        }

        if ($batchInvoice->isInvoiced()) {
            $unificationResponse = $this->unificationClient->sendPatchRequest($uniUrl, $uniPayload);
            $this->validateUnificationResponse($unificationResponse);
        }

        return $campaignInvoiceDTO;
    }

    private function prepareCaraUrl(): string
    {
        return sprintf(
            '%s/api/stochastic/invoice',
            $this->caraClient->getBaseUri(),
        );
    }

    private function prepareCaraPayload(CampaignInvoiceDTO $campaignInvoiceDTO): array
    {
        return [
            'accountIdentifier' => $campaignInvoiceDTO->accountIdentifier,
            'type' => $campaignInvoiceDTO->type,
            'quantityMailed' => $campaignInvoiceDTO->quantityMailed,
            'serviceUnitPrice' => $campaignInvoiceDTO->serviceUnitPrice,
            'postageUnitPrice' => $campaignInvoiceDTO->postageUnitPrice,
        ];
    }

    /**
     * @throws CaraAPIException
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    private function validateCaraResponse(ResponseInterface $response): void
    {
        if (Response::HTTP_OK !== $response->getStatusCode()) {
            $responseArray = $response->toArray();
            $errorMessage = $responseArray['errors']['detail'] ?? 'Unknown error.';

            throw new CaraAPIException($errorMessage);
        }
    }

    private function prepareUnificationUrl(CampaignInvoiceDTO $campaignInvoiceDTO): string
    {
        return sprintf(
            '%s/api/batch/%s',
            $this->unificationClient->getBaseUri(),
            $campaignInvoiceDTO->batchReference
        );
    }

    private function prepareUnificationPayload(string $batchStatusName): array
    {
        return [
            'batchStatusName' => $batchStatusName,
        ];
    }

    /**
     * @throws UnificationAPIException
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    private function validateUnificationResponse(ResponseInterface $response): void
    {
        if (Response::HTTP_OK !== $response->getStatusCode()) {
            $responseArray = $response->toArray();
            $errorMessage = $responseArray['errors']['detail'] ?? 'Unknown error.';

            throw new UnificationAPIException($errorMessage);
        }
    }
}
