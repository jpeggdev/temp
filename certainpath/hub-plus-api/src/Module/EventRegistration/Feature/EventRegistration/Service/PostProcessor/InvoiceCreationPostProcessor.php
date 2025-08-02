<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventRegistration\Service\PostProcessor;

use App\DTO\AuthNet\AuthNetChargeResponseDTO;
use App\Entity\Company;
use App\Entity\Employee;
use App\Entity\EventCheckout;
use App\Entity\Invoice;
use App\Entity\PaymentInvoice;
use App\Module\EventRegistration\Feature\EventRegistration\DTO\Request\ProcessPaymentRequestDTO;
use App\Module\EventRegistration\Feature\EventRegistration\Service\CreditMemoService;
use App\Module\EventRegistration\Feature\EventRegistration\Service\DiscountService;
use App\Module\EventRegistration\Feature\EventRegistration\Service\Factory\InvoiceFactory;
use App\Module\EventRegistration\Feature\EventRegistration\Service\Factory\InvoiceLineItemFactory;
use App\Module\EventRegistration\Feature\EventRegistration\Service\Factory\PaymentFactory;
use App\Module\EventRegistration\Feature\EventRegistration\Service\InvoiceNotesGenerator;
use Doctrine\ORM\EntityManagerInterface;

final readonly class InvoiceCreationPostProcessor implements EventCheckoutPostProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private InvoiceLineItemFactory $invoiceLineItemFactory,
        private DiscountService $discountService,
        private InvoiceFactory $invoiceFactory,
        private InvoiceNotesGenerator $invoiceNotesGenerator,
        private PaymentFactory $paymentFactory,
        private CreditMemoService $creditMemoService,
    ) {
    }

    public function postProcess(
        ProcessPaymentRequestDTO $dto,
        EventCheckout $eventCheckout,
        Company $company,
        Employee $employee,
        ?AuthNetChargeResponseDTO $chargeResponse = null,
    ): void {
        $invoice = $this->invoiceFactory->createInvoice($company, $eventCheckout, $dto);

        $session = $eventCheckout->getEventSession();
        $event = $session ? $session->getEvent() : null;
        $seatCost = $event ? $event->getEventPrice() : 0.0;

        $lineItemSubtotal = $this->createAttendeeLineItems($invoice, $eventCheckout, $seatCost);
        if ($lineItemSubtotal <= 0) {
            return;
        }

        $notes = $this->invoiceNotesGenerator->generateNotes($eventCheckout, $company, $chargeResponse);
        $invoice->setNotes($notes);

        $lineItemSubtotal = $this->discountService->applyDiscounts($invoice, $dto, $lineItemSubtotal);

        $finalTotal = max(0, $lineItemSubtotal);
        $invoice->setTotalAmount(number_format($finalTotal, 2, '.', ''));

        if ($chargeResponse && $chargeResponse->transactionId) {
            $this->createPaymentRecord($chargeResponse, $dto, $employee, $invoice, $finalTotal);
        }

        if ($dto->voucherQuantity && $dto->voucherQuantity > 0) {
            $this->creditMemoService->handleVoucherRedemption($invoice, $dto->voucherQuantity, $seatCost, $finalTotal);
        }

        $this->entityManager->persist($invoice);
    }

    private function createAttendeeLineItems(
        Invoice $invoice,
        EventCheckout $eventCheckout,
        float $seatCost,
    ): float {
        $lineItemSubtotal = 0.0;

        foreach ($eventCheckout->getEventCheckoutAttendees() as $attendee) {
            if ($attendee->isWaitlist()) {
                continue;
            }

            $lineItem = $this->invoiceLineItemFactory->createAttendeeLineItem($invoice, $attendee, $seatCost);
            $this->entityManager->persist($lineItem);

            $lineItemSubtotal += $seatCost;
        }

        return $lineItemSubtotal;
    }

    public function createPaymentRecord(
        AuthNetChargeResponseDTO $chargeResponse,
        ProcessPaymentRequestDTO $dto,
        Employee $employee,
        Invoice $invoice,
        mixed $finalTotal,
    ): void {
        $payment = $this->paymentFactory->createPayment($chargeResponse, $dto->amount, $employee);
        $this->entityManager->persist($payment);

        $paymentInvoice = new PaymentInvoice();
        $paymentInvoice->setPayment($payment);
        $paymentInvoice->setInvoice($invoice);
        $paymentInvoice->setAppliedAmount(number_format($finalTotal, 2, '.', ''));
        $this->entityManager->persist($paymentInvoice);
    }
}
