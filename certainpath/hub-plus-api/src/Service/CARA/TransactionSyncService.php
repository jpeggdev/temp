<?php

declare(strict_types=1);

namespace App\Service\CARA;

use App\Client\CaraClient;
use App\DTO\CARA\InvoiceDTO;
use App\DTO\CARA\TransactionDTO;
use App\Exception\CARA\CaraAPIException;
use App\Repository\InvoiceRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

readonly class TransactionSyncService
{
    public function __construct(
        private CaraClient $caraClient,
        private InvoiceRepository $invoiceRepository,
    ) {
    }

    /**
     * @throws CaraAPIException
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function syncTransactions(?array $invoices = null): void
    {
        if (null === $invoices) {
            $invoices = $this->invoiceRepository->findInvoicesForTransactionSync();
        }

        $payload = $this->prepareCaraPayload($invoices);
        $url = $this->prepareCaraUrl();
        $response = $this->caraClient->sendPostRequest($url, $payload);

        $this->validateCaraResponse($response);
        $successUuids = [];
        foreach ($response->toArray()['data'] ?? [] as $responseTransaction) {
            if ($invoiceUuid = $responseTransaction['data']['uuid'] ?? null) {
                $successUuids[] = $invoiceUuid;
            } else {
                throw new CaraAPIException('Transaction response does not contain invoice UUID.');
            }
        }

        foreach ($invoices as $invoice) {
            $invoice->incrementSyncAttempts();
            if (in_array($invoice->getUuid(), $successUuids, true)) {
                $invoice->setSyncedAt(new \DateTimeImmutable());
            }
            $this->invoiceRepository->save($invoice, true);
        }
    }

    public function mapInvoicesToTransactions(array $invoices): array
    {
        $transactions = [];
        foreach ($invoices as $invoice) {
            $accountingId = $invoice->getAccountingId();
            if (null === $accountingId) {
                throw new CaraAPIException(sprintf('Invoice %s has no accounting ID.', $invoice->getUuid()));
            }
            $invoiceDTO = InvoiceDTO::fromEntity($invoice);
            $transactionDTO = new TransactionDTO(
                $accountingId,
                $invoiceDTO
            );
            $transactions[] = self::normalizeObject($transactionDTO);
        }

        return $transactions;
    }

    private static function normalizeObject(mixed $object): mixed
    {
        if (is_array($object)) {
            return array_map([self::class, 'normalizeObject'], $object);
        }

        if (is_object($object)) {
            if ($object instanceof \DateTimeInterface) {
                return $object->format('c');
            }

            $result = [];
            foreach ((array) $object as $key => $value) {
                $key = preg_replace('/^\0.*\0/', '', $key);
                $result[$key] = self::normalizeObject($value);
            }

            return $result;
        }

        return $object;
    }

    private function prepareCaraUrl(): string
    {
        return sprintf(
            '%s/api/hubevent/transactions',
            $this->caraClient->getBaseUri(),
        );
    }

    /**
     * @throws CaraAPIException
     */
    private function prepareCaraPayload(array $invoices): array
    {
        $payload['hubEventTransactions'] = $this->mapInvoicesToTransactions($invoices);
        if (empty($payload['hubEventTransactions'])) {
            throw new CaraAPIException('No transactions to sync.');
        }

        return $payload;
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
}
