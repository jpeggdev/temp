<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventVoucherManagement\Service;

use App\Entity\EventVoucher;
use App\Module\EventRegistration\Feature\EventVoucherManagement\DTO\Request\CreateEventVoucherDTO;
use App\Module\EventRegistration\Feature\EventVoucherManagement\DTO\Response\GetEventVoucherResponseDTO;
use App\Repository\CompanyRepository;
use App\Repository\CreditMemoLineItemRepository;
use App\Repository\EventVoucherRepository;

readonly class CreateEventVoucherService extends BaseEventVoucherService
{
    public function __construct(
        CreditMemoLineItemRepository $creditMemoLineItemRepository,
        private CompanyRepository $companyRepository,
        private EventVoucherRepository $eventVoucherRepository,
    ) {
        parent::__construct($creditMemoLineItemRepository);
    }

    public function createVoucher(CreateEventVoucherDTO $dto): GetEventVoucherResponseDTO
    {
        $company = $this->companyRepository->findOneByIdentifierOrFail($dto->companyIdentifier);

        $eventVoucher = (new EventVoucher())
            ->setCompany($company)
            ->setName($dto->name)
            ->setDescription($dto->description)
            ->setStartDate($dto->startDate)
            ->setEndDate($dto->endDate)
            ->setIsActive($dto->isActive)
            ->setTotalSeats($dto->totalSeats);

        $this->eventVoucherRepository->save($eventVoucher, true);

        $eventVoucherUsage = $this->resolveEventVoucherUsage($eventVoucher);
        $availableSeats = $this->resolveEventVoucherAvailableSeats($eventVoucher);

        return GetEventVoucherResponseDTO::fromEntity($eventVoucher, $eventVoucherUsage, $availableSeats);
    }
}
