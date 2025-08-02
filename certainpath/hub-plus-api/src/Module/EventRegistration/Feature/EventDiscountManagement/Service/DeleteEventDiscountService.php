<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventDiscountManagement\Service;

use App\Module\EventRegistration\Feature\EventDiscountManagement\DTO\Response\GetEventDiscountResponseDTO;
use App\Repository\EventDiscountRepository;
use App\Repository\InvoiceLineItemRepository;

readonly class DeleteEventDiscountService extends BaseEventDiscountService
{
    public function __construct(
        private EventDiscountRepository $eventDiscountRepository,
        InvoiceLineItemRepository $invoiceLineItemRepository,
    ) {
        parent::__construct($invoiceLineItemRepository);
    }

    public function deleteEventDiscount(int $id): GetEventDiscountResponseDTO
    {
        $eventDiscountToDelete = $this->eventDiscountRepository->findOneByIdOrFail($id);

        $this->eventDiscountRepository->softDelete($eventDiscountToDelete, true);
        $eventDiscountUsage = $this->resolveEventDiscountUsage($eventDiscountToDelete);

        return GetEventDiscountResponseDTO::fromEntity(
            $eventDiscountToDelete,
            $eventDiscountUsage
        );
    }
}
