<?php

declare(strict_types=1);

namespace App\Module\ServiceTitan\ValueObject;

/**
 * Represents a customer response from the ServiceTitan API
 */
class CustomerResponse
{
    public function __construct(
        private readonly bool $success,
        /** @var array<string, mixed> */
        private readonly array $data,
        private readonly int $statusCode,
        private readonly ?string $error = null
    ) {
    }

    public function isSuccess(): bool
    {
        return $this->success;
    }

    public function isFailure(): bool
    {
        return !$this->success;
    }

    /**
     * Get raw customer data from ServiceTitan API
     *
     * @return array<string, mixed>
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * Get customer records from the response
     *
     * @return array<array<string, mixed>>
     */
    public function getCustomers(): array
    {
        return $this->data['data'] ?? [];
    }

    /**
     * Get pagination information
     *
     * @return array<string, mixed>
     */
    public function getPagination(): array
    {
        return [
            'hasMore' => $this->data['hasMore'] ?? false,
            'totalCount' => $this->data['totalCount'] ?? 0,
            'page' => $this->data['page'] ?? 1,
            'pageSize' => $this->data['pageSize'] ?? 50,
        ];
    }

    public function hasMore(): bool
    {
        return $this->data['hasMore'] ?? false;
    }

    public function getTotalCount(): int
    {
        return $this->data['totalCount'] ?? 0;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getError(): ?string
    {
        return $this->error;
    }
}
