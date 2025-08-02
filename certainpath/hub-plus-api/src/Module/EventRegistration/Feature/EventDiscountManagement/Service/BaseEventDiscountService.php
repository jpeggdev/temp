<?php

namespace App\Module\EventRegistration\Feature\EventDiscountManagement\Service;

use App\Entity\EventDiscount;
use App\Repository\InvoiceLineItemRepository;

readonly class BaseEventDiscountService
{
    public function __construct(
        protected InvoiceLineItemRepository $invoiceLineItemRepository,
    ) {
    }

    protected function resolveEventDiscountUsage(EventDiscount $eventDiscount): string
    {
        $totalUses = $eventDiscount->getMaximumUses()
            ? (string) $eventDiscount->getMaximumUses()
            : '∞';

        $totalDiscountsUsed = $this->invoiceLineItemRepository->countInvoiceLineItemsByDiscountCode(
            $eventDiscount->getCode()
        );

        return $totalDiscountsUsed.' / '.$totalUses;
    }
}
