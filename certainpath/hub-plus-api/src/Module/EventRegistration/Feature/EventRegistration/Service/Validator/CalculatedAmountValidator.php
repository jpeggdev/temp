<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventRegistration\Service\Validator;

use App\Entity\Company;
use App\Entity\Employee;
use App\Entity\EventCheckout;
use App\Module\EventRegistration\Feature\EventRegistration\DTO\Request\ProcessPaymentRequestDTO;
use App\Module\EventRegistration\Feature\EventRegistration\Exception\NoEventFoundException;
use App\Module\EventRegistration\Feature\EventRegistration\Exception\NoEventSessionFoundException;
use App\Module\EventRegistration\Feature\EventRegistration\Exception\PaymentAmountMismatchException;
use App\Repository\EventDiscountRepository;

final readonly class CalculatedAmountValidator implements EventCheckoutValidatorInterface
{
    public function __construct(
        private EventDiscountRepository $eventDiscountRepository,
    ) {
    }

    public function validate(
        ProcessPaymentRequestDTO $dto,
        EventCheckout $eventCheckout,
        Company $company,
        Employee $employee,
    ): void {
        $session = $eventCheckout->getEventSession();
        if (!$session) {
            throw new NoEventSessionFoundException();
        }

        $event = $session->getEvent();
        if (!$event) {
            throw new NoEventFoundException('No Event found for this session.');
        }

        $nonWaitlistedAttendees = array_filter(
            $eventCheckout->getEventCheckoutAttendees()->toArray(),
            fn ($attendee) => !$attendee->isWaitlist()
        );
        $attendeeCount = count($nonWaitlistedAttendees);

        $eventPrice = $event->getEventPrice();
        $baseCost = $attendeeCount * $eventPrice;

        $voucherQuantity = $dto->voucherQuantity ?? 0;
        $voucherCoverage = $voucherQuantity * $eventPrice;

        $discountCoverage = 0.0;
        if ($dto->discountCode && $dto->discountAmount && $dto->discountAmount > 0) {
            $discountEntity = $this->eventDiscountRepository->findOneByCode($dto->discountCode);
            $isPercentage = false;

            if ($discountEntity && $discountEntity->getDiscountType()) {
                $discountTypeEntityName = $discountEntity->getDiscountType()->getName();
                if ('percentage' === $discountTypeEntityName) {
                    $isPercentage = true;
                }
            }
            if ($isPercentage) {
                $discountCoverage = $baseCost * ((float) $dto->discountAmount / 100.0);
            } else {
                $discountCoverage = (float) $dto->discountAmount;
            }
        }

        $adminCoverage = 0.0;
        if ($dto->adminDiscountType && $dto->adminDiscountValue && $dto->adminDiscountValue > 0) {
            if ('percentage' === $dto->adminDiscountType) {
                $adminCoverage = $baseCost * ($dto->adminDiscountValue / 100.0);
            } else {
                $adminCoverage = (float) $dto->adminDiscountValue;
            }
        }

        $totalCoverage = $voucherCoverage + $discountCoverage + $adminCoverage;
        $calculated = $baseCost - $totalCoverage;
        if ($calculated < 0.0) {
            $calculated = 0.0;
        }

        $expected = (float) number_format($calculated, 2, '.', '');
        $actual = (float) number_format($dto->amount, 2, '.', '');

        // If computed and actual differ by more than a small epsilon, throw
        if (abs($expected - $actual) > 0.001) {
            throw new PaymentAmountMismatchException("Payment amount mismatch. Expected: {$expected}, Got: {$actual}");
        }
    }
}
