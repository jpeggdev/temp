<?php

namespace App\DTO\CARA;

class TransactionDTO
{
    public function __construct(
        public string $accountingId,
        public InvoiceDTO $invoice,
    ) {
    }
}
