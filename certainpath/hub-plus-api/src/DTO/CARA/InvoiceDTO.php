<?php

namespace App\DTO\CARA;

use App\Entity\Invoice;
use Symfony\Component\Validator\Constraints as Assert;

class InvoiceDTO
{
    public const TYPE_EVENT = 'event';
    public \DateTimeImmutable $createdAt;

    public function __construct(
        #[Assert\NotBlank(message: 'accountIdentifier should not be blank.')]
        public string $accountIdentifier,
        public string $type,
        public ?PaymentDTO $payment,
        public ?CreditMemoDTO $creditMemo,
        public array $lines,
        public string $status,
        public string $totalAmount,
        public string $notes,
        string|\DateTimeImmutable $createdAt,
        public string $uuid,
        public string $invoiceNumber,
    ) {
        if (is_string($createdAt)) {
            try {
                $createdAt = new \DateTimeImmutable($createdAt);
            } catch (\Exception) {
                $createdAt = new \DateTimeImmutable();
            }
        }
        $this->createdAt = $createdAt;
    }

    public static function fromEntity(Invoice $entity): self
    {
        // Convert invoice line items
        $lines = [];
        foreach ($entity->getInvoiceLineItems() as $lineItem) {
            $lines[] = InvoiceLineItemDTO::fromEntity($lineItem);
        }

        // Get the first payment (assuming one payment per invoice for DTO)
        $paymentDTO = null;

        if (false === $entity->getPaymentInvoices()->isEmpty()) {
            $firstPaymentInvoice = $entity->getPaymentInvoices()->first();
            if ($firstPaymentInvoice && $firstPaymentInvoice->getPayment()) {
                $paymentDTO = PaymentDTO::fromEntity($firstPaymentInvoice->getPayment());
            }
        }

        // Get the first credit memo (assuming one credit memo per invoice for DTO)
        $creditMemoDTO = null;
        if (!$entity->getCreditMemos()->isEmpty()) {
            $firstCreditMemo = $entity->getCreditMemos()->first();
            if ($firstCreditMemo) {
                $creditMemoDTO = CreditMemoDTO::fromEntity($firstCreditMemo);
            }
        }

        return new self(
            $entity->getCompany()?->getIntacctId() ?? $entity->getCompany()?->getSalesforceId() ?? '',
            self::TYPE_EVENT,
            $paymentDTO,
            $creditMemoDTO,
            $lines,
            $entity->getStatus()->value,
            $entity->getTotalAmount(),
            $entity->getNotes() ?? '',
            $entity->getCreatedAt(),
            $entity->getUuid(),
            $entity->getInvoiceNumber()
        );
    }
}
