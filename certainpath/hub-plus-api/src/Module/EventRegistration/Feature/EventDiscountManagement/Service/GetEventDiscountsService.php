<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventDiscountManagement\Service;

use App\Module\EventRegistration\Feature\EventDiscountManagement\DTO\Query\GetEventDiscountsDTO;
use App\Module\EventRegistration\Feature\EventDiscountManagement\DTO\Response\GetEventDiscountResponseDTO;
use App\Repository\EventDiscountRepository;
use App\Repository\InvoiceLineItemRepository;

readonly class GetEventDiscountsService extends BaseEventDiscountService
{
    public function __construct(
        private EventDiscountRepository $eventDiscountRepository,
        InvoiceLineItemRepository $invoiceLineItemRepository,
    ) {
        parent::__construct($invoiceLineItemRepository);
    }

    public function getDiscount(int $id): GetEventDiscountResponseDTO
    {
        $eventDiscount = $this->eventDiscountRepository->findOneByIdOrFail($id);
        $discountUsage = $this->resolveEventDiscountUsage($eventDiscount);

        return GetEventDiscountResponseDTO::fromEntity(
            $eventDiscount,
            $discountUsage
        );
    }

    public function getDiscounts(GetEventDiscountsDTO $queryDto): array
    {
        $eventDiscounts = $this->eventDiscountRepository->findAllByDTO($queryDto);
        $totalCount = $this->eventDiscountRepository->getCountByDTO($queryDto);
        $eventDiscountDTOs = [];

        foreach ($eventDiscounts as $eventDiscount) {
            $discountUsage = $this->resolveEventDiscountUsage($eventDiscount);
            $eventDiscountDTOs[] = GetEventDiscountResponseDTO::fromEntity(
                $eventDiscount,
                $discountUsage
            );
        }

        return [
            'eventDiscounts' => $eventDiscountDTOs,
            'totalCount' => $totalCount,
        ];
    }
}
