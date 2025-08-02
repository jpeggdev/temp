<?php

namespace App\Module\EventRegistration\Feature\EventVoucherManagement\Service;

use App\Entity\EventVoucher;
use App\Repository\CreditMemoLineItemRepository;

readonly class BaseEventVoucherService
{
    public function __construct(
        private CreditMemoLineItemRepository $creditMemoLineItemRepository,
    ) {
    }

    protected function resolveEventVoucherUsage(EventVoucher $eventVoucher): string
    {
        $totalSeats = $eventVoucher->getTotalSeats() ? (string) $eventVoucher->getTotalSeats() : '∞';

        $totalUsedSeats = $this->creditMemoLineItemRepository->countVoucherLineItemsForCompany(
            $eventVoucher->getCompany()
        );

        return $totalUsedSeats.' / '.$totalSeats;
    }

    protected function resolveEventVoucherAvailableSeats(EventVoucher $eventVoucher): int
    {
        $totalUsedSeats = $this->creditMemoLineItemRepository->countVoucherLineItemsForCompany(
            $eventVoucher->getCompany()
        );

        return (int) $eventVoucher->getTotalSeats() - $totalUsedSeats;
    }
}
