<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventVoucherManagement\Service;

use App\Module\EventRegistration\Feature\EventVoucherManagement\DTO\Response\GetEventVoucherResponseDTO;
use App\Repository\CreditMemoLineItemRepository;
use App\Repository\EventVoucherRepository;

readonly class DeleteEventVoucherService extends BaseEventVoucherService
{
    public function __construct(
        private EventVoucherRepository $eventVoucherRepository,
        CreditMemoLineItemRepository $creditMemoLineItemRepository,
    ) {
        parent::__construct($creditMemoLineItemRepository);
    }

    public function deleteEventVoucher(int $id): GetEventVoucherResponseDTO
    {
        $eventVoucherToDelete = $this->eventVoucherRepository->findOneByIdOrFail($id);
        $eventVoucherUsage = $this->resolveEventVoucherUsage($eventVoucherToDelete);
        $availableSeats = $this->resolveEventVoucherAvailableSeats($eventVoucherToDelete);

        $this->eventVoucherRepository->softDelete($eventVoucherToDelete, true);

        return GetEventVoucherResponseDTO::fromEntity(
            $eventVoucherToDelete,
            $eventVoucherUsage,
            $availableSeats
        );
    }
}
