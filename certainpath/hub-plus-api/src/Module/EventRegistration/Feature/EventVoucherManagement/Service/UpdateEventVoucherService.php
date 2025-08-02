<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventVoucherManagement\Service;

use App\Module\EventRegistration\Feature\EventVoucherManagement\DTO\Request\UpdateEventVoucherDTO;
use App\Module\EventRegistration\Feature\EventVoucherManagement\DTO\Response\GetEventVoucherResponseDTO;
use App\Module\EventRegistration\Feature\EventVoucherManagement\Exception\EventVoucherUpdateException;
use App\Repository\CreditMemoLineItemRepository;
use App\Repository\EventVoucherRepository;

readonly class UpdateEventVoucherService extends BaseEventVoucherService
{
    public function __construct(
        private EventVoucherRepository $eventVoucherRepository,
        CreditMemoLineItemRepository $creditMemoLineItemRepository,
    ) {
        parent::__construct($creditMemoLineItemRepository);
    }

    public function updateVoucher(
        int $venueId,
        UpdateEventVoucherDTO $dto,
    ): GetEventVoucherResponseDTO {
        $eventVoucher = $this->eventVoucherRepository->findOneByIdOrFail($venueId);

        if (!$eventVoucher->isActive() || $eventVoucher->getDeletedAt()) {
            throw new EventVoucherUpdateException(message: 'The voucher has already been deleted.');
        }

        $eventVoucher
            ->setName($dto->name)
            ->setDescription($dto->description)
            ->setStartDate($dto->startDate)
            ->setEndDate($dto->endDate)
            ->setIsActive($dto->isActive);

        $this->eventVoucherRepository->save($eventVoucher, true);

        $eventVoucherUsage = $this->resolveEventVoucherUsage($eventVoucher);
        $availableSeats = $this->resolveEventVoucherAvailableSeats($eventVoucher);

        return GetEventVoucherResponseDTO::fromEntity(
            $eventVoucher,
            $eventVoucherUsage,
            $availableSeats
        );
    }
}
