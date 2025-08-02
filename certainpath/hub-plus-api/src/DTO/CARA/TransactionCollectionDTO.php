<?php

namespace App\DTO\CARA;

use Symfony\Component\Validator\Constraints as Assert;

class TransactionCollectionDTO
{
    /**
     * @var TransactionDTO[]
     */
    #[Assert\Valid]
    #[Assert\Count(min: 1, minMessage: 'At least one TransactionDTO is required')]
    public array $Transactions = [];

    public function addTransactionDTO(TransactionDTO $transaction): self
    {
        $this->Transactions[] = $transaction;

        return $this;
    }

    public function getTransactionDTOs(): array
    {
        return $this->Transactions;
    }
}
