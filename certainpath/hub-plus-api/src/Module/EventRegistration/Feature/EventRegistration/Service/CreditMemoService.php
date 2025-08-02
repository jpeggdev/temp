<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventRegistration\Service;

use App\Entity\Invoice;
use App\Enum\CreditMemoType;
use App\Module\EventRegistration\Feature\EventRegistration\Service\Factory\CreditMemoFactory;
use App\Module\EventRegistration\Feature\EventRegistration\Service\Factory\CreditMemoLineItemFactory;
use Doctrine\ORM\EntityManagerInterface;

final readonly class CreditMemoService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private CreditMemoFactory $creditMemoFactory,
        private CreditMemoLineItemFactory $creditMemoLineItemFactory,
    ) {
    }

    /**
     * Handle voucher redemption by creating a CreditMemo and line items.
     */
    public function handleVoucherRedemption(
        Invoice $invoice,
        int $voucherQuantity,
        float $seatCost,
        float $finalTotal,
    ): void {
        $maxApplicableVouchers = min(
            $voucherQuantity,
            (int) floor($finalTotal / $seatCost)
        );

        if ($maxApplicableVouchers <= 0) {
            return;
        }

        $creditMemo = $this->creditMemoFactory->createCreditMemo(
            $invoice,
            CreditMemoType::VOUCHER,
            new \DateTimeImmutable(),
            sprintf('Voucher redemption (%d vouchers)', $voucherQuantity)
        );

        $totalVoucherAmount = 0.0;
        for ($i = 0; $i < $maxApplicableVouchers; ++$i) {
            $description = sprintf('Voucher redemption (#%d)', $i + 1);
            $lineItem = $this->creditMemoLineItemFactory->createCreditMemoLineItem(
                $creditMemo,
                $description,
                $seatCost
            );
            $this->entityManager->persist($lineItem);

            $totalVoucherAmount += $seatCost;
        }

        $remainingAmount = $finalTotal - $totalVoucherAmount;
        if ($remainingAmount > 0 && $remainingAmount < $seatCost && $maxApplicableVouchers < $voucherQuantity) {
            $description = sprintf('Partial voucher redemption (#%d)', $maxApplicableVouchers + 1);
            $partialLineItem = $this->creditMemoLineItemFactory->createCreditMemoLineItem(
                $creditMemo,
                $description,
                $remainingAmount
            );
            $this->entityManager->persist($partialLineItem);

            $totalVoucherAmount += $remainingAmount;
        }

        $creditMemo->setTotalAmount(number_format($totalVoucherAmount, 2, '.', ''));
        $this->entityManager->persist($creditMemo);
    }
}
