<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventRegistration\Service;

use App\Entity\Invoice;
use App\Module\EventRegistration\Feature\EventRegistration\DTO\Request\ProcessPaymentRequestDTO;
use App\Module\EventRegistration\Feature\EventRegistration\Service\Factory\InvoiceLineItemFactory;
use Doctrine\ORM\EntityManagerInterface;

final readonly class DiscountService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private InvoiceLineItemFactory $invoiceLineItemFactory,
    ) {
    }

    /**
     * Apply both non-admin and admin discounts to the Invoice, returning the new subtotal.
     */
    public function applyDiscounts(
        Invoice $invoice,
        ProcessPaymentRequestDTO $dto,
        float $lineItemSubtotal,
    ): float {
        $baseLineItemSubtotal = $lineItemSubtotal;

        $nonAdminDiscount = $this->calculateNonAdminDiscount($dto);
        $adminDiscountAmount = $this->calculateAdminDiscount($dto, $baseLineItemSubtotal);

        if ($nonAdminDiscount > 0) {
            $discountDesc = 'Discount';
            if ($dto->discountCode) {
                $discountDesc = sprintf('Discount (%s)', $dto->discountCode);
            }

            $discountLine = $this->invoiceLineItemFactory->createDiscountLineItem(
                $invoice,
                $discountDesc,
                $nonAdminDiscount,
                $dto->discountCode
            );
            $this->entityManager->persist($discountLine);

            $lineItemSubtotal -= $nonAdminDiscount;
        }

        if ($adminDiscountAmount > 0) {
            $adminDiscountDesc = 'Administrative Discount';
            if ($dto->adminDiscountReason) {
                $adminDiscountDesc .= sprintf(' (%s)', $dto->adminDiscountReason);
            }

            $adminDiscountLine = $this->invoiceLineItemFactory->createDiscountLineItem(
                $invoice,
                $adminDiscountDesc,
                $adminDiscountAmount
            );
            $this->entityManager->persist($adminDiscountLine);

            $lineItemSubtotal -= $adminDiscountAmount;
        }

        return $lineItemSubtotal;
    }

    private function calculateNonAdminDiscount(ProcessPaymentRequestDTO $dto): float
    {
        if ($dto->discountAmount && $dto->discountAmount > 0) {
            return $dto->discountAmount;
        }

        return 0.0;
    }

    private function calculateAdminDiscount(ProcessPaymentRequestDTO $dto, float $baseLineItemSubtotal): float
    {
        if (!$dto->adminDiscountValue || $dto->adminDiscountValue <= 0) {
            return 0.0;
        }

        if ('percentage' === $dto->adminDiscountType) {
            return $baseLineItemSubtotal * ($dto->adminDiscountValue / 100);
        } elseif ('fixed_amount' === $dto->adminDiscountType) {
            return $dto->adminDiscountValue;
        }

        return 0.0;
    }
}
