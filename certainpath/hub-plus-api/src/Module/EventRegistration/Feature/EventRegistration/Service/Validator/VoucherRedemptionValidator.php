<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventRegistration\Service\Validator;

use App\Entity\Company;
use App\Entity\Employee;
use App\Entity\EventCheckout;
use App\Module\EventRegistration\Feature\EventRegistration\DTO\Request\ProcessPaymentRequestDTO;
use App\Module\EventRegistration\Feature\EventRegistration\Exception\EventNotEligibleForVoucherException;
use App\Module\EventRegistration\Feature\EventRegistration\Exception\InsufficientVoucherSeatsException;
use App\Repository\CreditMemoLineItemRepository;
use App\Repository\EventVoucherRepository;

final readonly class VoucherRedemptionValidator implements EventCheckoutValidatorInterface
{
    public function __construct(
        private EventVoucherRepository $eventVoucherRepository,
        private CreditMemoLineItemRepository $creditMemoLineItemRepository,
    ) {
    }

    public function validate(
        ProcessPaymentRequestDTO $dto,
        EventCheckout $eventCheckout,
        Company $company,
        Employee $employee,
    ): void {
        $voucherQty = $dto->voucherQuantity ?? 0;
        if ($voucherQty <= 0) {
            return;
        }

        $eventSession = $eventCheckout->getEventSession();
        $event = $eventSession?->getEvent();
        if (!$event || !$event->isVoucherEligible()) {
            throw new EventNotEligibleForVoucherException();
        }

        $now = new \DateTimeImmutable();
        $vouchers = $this->eventVoucherRepository->findAllByCompany($company);

        $totalAvailableSeats = 0;
        foreach ($vouchers as $voucher) {
            if (
                $voucher->isActive()
                && (!$voucher->getStartDate() || $voucher->getStartDate() <= $now)
                && (!$voucher->getEndDate() || $voucher->getEndDate() >= $now)
            ) {
                $totalAvailableSeats += $voucher->getTotalSeats() ?: 0;
            }
        }

        $usedSeats = $this->creditMemoLineItemRepository->countVoucherLineItemsForCompany($company);
        $remaining = max($totalAvailableSeats - $usedSeats, 0);

        if ($voucherQty > $remaining) {
            throw new InsufficientVoucherSeatsException();
        }
    }
}
