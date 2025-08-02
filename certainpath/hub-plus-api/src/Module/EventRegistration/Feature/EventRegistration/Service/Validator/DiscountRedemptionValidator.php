<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventRegistration\Service\Validator;

use App\Entity\Company;
use App\Entity\Employee;
use App\Entity\EventCheckout;
use App\Module\EventRegistration\Feature\EventRegistration\DTO\Request\ProcessPaymentRequestDTO;
use App\Module\EventRegistration\Feature\EventRegistration\Exception\DiscountCodeExpiredException;
use App\Module\EventRegistration\Feature\EventRegistration\Exception\DiscountCodeNotYetActiveException;
use App\Module\EventRegistration\Feature\EventRegistration\Exception\DiscountNotValidForEventException;
use App\Module\EventRegistration\Feature\EventRegistration\Exception\DiscountReachedMaxUsageException;
use App\Module\EventRegistration\Feature\EventRegistration\Exception\InvalidDiscountCodeException;
use App\Module\EventRegistration\Feature\EventRegistration\Exception\MinimumPurchaseNotMetException;
use App\Module\EventRegistration\Feature\EventRegistration\Exception\NoEventFoundException;
use App\Repository\EventDiscountRepository;
use App\Repository\InvoiceLineItemRepository;

final readonly class DiscountRedemptionValidator implements EventCheckoutValidatorInterface
{
    public function __construct(
        private EventDiscountRepository $eventDiscountRepository,
        private InvoiceLineItemRepository $invoiceLineItemRepository,
    ) {
    }

    public function validate(
        ProcessPaymentRequestDTO $dto,
        EventCheckout $eventCheckout,
        Company $company,
        Employee $employee,
    ): void {
        if (!$dto->discountCode) {
            return;
        }

        $eventSession = $eventCheckout->getEventSession();
        $event = $eventSession?->getEvent();
        if (!$event) {
            throw new NoEventFoundException();
        }

        $discount = $this->eventDiscountRepository->findOneByCode($dto->discountCode);
        if (!$discount || !$discount->isActive()) {
            throw new InvalidDiscountCodeException();
        }

        $now = new \DateTimeImmutable();
        if ($discount->getStartDate() && $discount->getStartDate() > $now) {
            throw new DiscountCodeNotYetActiveException();
        }
        if ($discount->getEndDate() && $discount->getEndDate() < $now) {
            throw new DiscountCodeExpiredException();
        }

        $mappings = $discount->getEventEventDiscounts();
        if ($mappings->count() > 0) {
            $applies = false;
            foreach ($mappings as $map) {
                if ($map->getEvent() && $map->getEvent()->getId() === $event->getId()) {
                    $applies = true;
                    break;
                }
            }
            if (!$applies) {
                throw new DiscountNotValidForEventException();
            }
        }

        $maxUses = $discount->getMaximumUses();
        if (null !== $maxUses) {
            $usesCount = $this->invoiceLineItemRepository
                ->countInvoiceLineItemsByDiscountCode($discount->getCode());
            if ($usesCount >= $maxUses) {
                throw new DiscountReachedMaxUsageException();
            }
        }

        $attendeeCount = $eventCheckout->getEventCheckoutAttendees()->count();
        $checkoutSubtotal = $attendeeCount * $event->getEventPrice();
        if (null !== $discount->getMinimumPurchaseAmount()) {
            $minPurchase = (float) $discount->getMinimumPurchaseAmount();
            if ($checkoutSubtotal < $minPurchase) {
                throw new MinimumPurchaseNotMetException();
            }
        }
    }
}
