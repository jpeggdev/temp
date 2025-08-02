<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventRegistration\Service\Factory;

use App\Entity\EventCheckoutAttendee;
use App\Entity\Invoice;
use App\Entity\InvoiceLineItem;

final class InvoiceLineItemFactory
{
    /**
     * Create a line item for a single attendee registration.
     */
    public function createAttendeeLineItem(
        Invoice $invoice,
        EventCheckoutAttendee $attendee,
        float $seatCost,
    ): InvoiceLineItem {
        $description = sprintf(
            'Registration for %s %s',
            $attendee->getFirstName() ?: '',
            $attendee->getLastName() ?: ''
        );

        $lineItem = new InvoiceLineItem();
        $lineItem->setInvoice($invoice);
        $lineItem->setDescription(trim($description));
        $lineItem->setQuantity(1);
        $lineItem->setUnitPrice(number_format($seatCost, 2, '.', ''));
        $lineItem->setLineTotal(number_format($seatCost, 2, '.', ''));

        return $lineItem;
    }

    /**
     * Create a line item for a discount (negative amount).
     */
    public function createDiscountLineItem(
        Invoice $invoice,
        string $description,
        float $discountAmount,
        ?string $discountCode = null,
    ): InvoiceLineItem {
        $lineItem = new InvoiceLineItem();
        $lineItem->setInvoice($invoice);
        $lineItem->setDescription($description);
        $lineItem->setQuantity(1);
        $lineItem->setUnitPrice(number_format(-$discountAmount, 2, '.', ''));
        $lineItem->setLineTotal(number_format(-$discountAmount, 2, '.', ''));
        $lineItem->setDiscountCode($discountCode);

        return $lineItem;
    }
}
