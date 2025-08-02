<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventVoucherManagement\Service;

use App\Module\EventRegistration\Feature\EventVoucherManagement\DTO\Query\GetEventVouchersDTO;
use App\Module\EventRegistration\Feature\EventVoucherManagement\DTO\Response\GetEventVoucherResponseDTO;
use App\Repository\CreditMemoLineItemRepository;
use App\Repository\EventVoucherRepository;

readonly class GetEventVoucherService extends BaseEventVoucherService
{
    public function __construct(
        private EventVoucherRepository $eventVoucherRepository,
        CreditMemoLineItemRepository $creditMemoLineItemRepository,
    ) {
        parent::__construct($creditMemoLineItemRepository);
    }

    public function getVoucher(int $id): GetEventVoucherResponseDTO
    {
        $eventVoucher = $this->eventVoucherRepository->findOneByIdOrFail($id);
        $eventVoucherUsage = $this->resolveEventVoucherUsage($eventVoucher);
        $eventVoucherAvailableSeats = $this->resolveEventVoucherAvailableSeats($eventVoucher);

        return GetEventVoucherResponseDTO::fromEntity(
            $eventVoucher,
            $eventVoucherUsage,
            $eventVoucherAvailableSeats,
        );
    }

    public function getVouchers(GetEventVouchersDTO $queryDto): array
    {
        $eventVouchers = $this->eventVoucherRepository->findAllByDTO($queryDto);
        $totalCount = $this->eventVoucherRepository->getCountByDTO($queryDto);
        $eventVoucherDTOs = [];

        foreach ($eventVouchers as $eventVoucher) {
            $eventVoucherUsage = $this->resolveEventVoucherUsage($eventVoucher);
            $availableSeats = $this->resolveEventVoucherAvailableSeats($eventVoucher);
            $eventVoucherDTOs[] = GetEventVoucherResponseDTO::fromEntity(
                $eventVoucher,
                $eventVoucherUsage,
                $availableSeats
            );
        }

        return [
            'eventVouchers' => $eventVoucherDTOs,
            'totalCount' => $totalCount,
        ];
    }
}
