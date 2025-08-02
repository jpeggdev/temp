<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventRegistration\Service\Factory;

use App\Entity\CreditMemo;
use App\Entity\Invoice;
use App\Enum\CreditMemoStatusType;
use App\Enum\CreditMemoType;

final class CreditMemoFactory
{
    /**
     * Create a new CreditMemo entity with basic fields set.
     */
    public function createCreditMemo(
        Invoice $invoice,
        CreditMemoType $type,
        \DateTimeImmutable $date,
        string $reason,
    ): CreditMemo {
        $creditMemo = new CreditMemo();
        $creditMemo->setType($type);
        $creditMemo->setInvoice($invoice);
        $creditMemo->setCmDate($date);
        $creditMemo->setStatus(CreditMemoStatusType::POSTED);
        $creditMemo->setReason($reason);
        $creditMemo->setTotalAmount('0.00');

        return $creditMemo;
    }
}
