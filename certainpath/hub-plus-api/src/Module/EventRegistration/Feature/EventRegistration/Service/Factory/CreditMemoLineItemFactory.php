<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventRegistration\Service\Factory;

use App\Entity\CreditMemo;
use App\Entity\CreditMemoLineItem;

final class CreditMemoLineItemFactory
{
    /**
     * Create a CreditMemoLineItem with the given description and amount.
     */
    public function createCreditMemoLineItem(
        CreditMemo $creditMemo,
        string $description,
        float $amount,
    ): CreditMemoLineItem {
        $lineItem = new CreditMemoLineItem();
        $lineItem->setCreditMemo($creditMemo);
        $lineItem->setDescription($description);
        $lineItem->setAmount(number_format($amount, 2, '.', ''));

        return $lineItem;
    }
}
