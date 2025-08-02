<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventRegistration\Service\Factory;

use App\Entity\Company;
use App\Entity\EventCheckout;
use App\Entity\Invoice;
use App\Enum\InvoiceStatusType;
use App\Module\EventRegistration\Feature\EventRegistration\DTO\Request\ProcessPaymentRequestDTO;

final class InvoiceFactory
{
    /**
     * Create a new Invoice entity.
     */
    public function createInvoice(
        Company $company,
        EventCheckout $eventCheckout,
        ProcessPaymentRequestDTO $dto,
    ): Invoice {
        $invoice = new Invoice();
        $invoice->setCompany($company);
        $invoice->setEventSession($eventCheckout->getEventSession());
        $invoice->setInvoiceDate(new \DateTimeImmutable());
        $invoice->setStatus(InvoiceStatusType::POSTED);
        $invoice->setTotalAmount('0.00');
        $invoice->setInvoiceNumber($dto->invoiceNumber);

        return $invoice;
    }
}
