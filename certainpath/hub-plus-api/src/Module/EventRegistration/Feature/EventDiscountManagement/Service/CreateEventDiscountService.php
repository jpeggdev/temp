<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventDiscountManagement\Service;

use App\Entity\EventDiscount;
use App\Entity\EventEventDiscount;
use App\Module\EventRegistration\Feature\EventDiscountManagement\DTO\Request\CreateUpdateEventDiscountDTO;
use App\Module\EventRegistration\Feature\EventDiscountManagement\DTO\Response\GetEventDiscountResponseDTO;
use App\Repository\DiscountTypeRepository;
use App\Repository\EventDiscountRepository;
use App\Repository\EventRepository\EventRepository;
use App\Repository\InvoiceLineItemRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;

readonly class CreateEventDiscountService extends BaseEventDiscountService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private EventRepository $eventRepository,
        private DiscountTypeRepository $discountTypeRepository,
        private EventDiscountRepository $eventDiscountRepository,
        InvoiceLineItemRepository $invoiceLineItemRepository,
    ) {
        parent::__construct($invoiceLineItemRepository);
    }

    public function createDiscount(CreateUpdateEventDiscountDTO $dto): GetEventDiscountResponseDTO
    {
        $discountType = $this->discountTypeRepository->findOneByIdOrFail($dto->discountTypeId);

        $events = new ArrayCollection();
        foreach ($dto->eventIds as $eventId) {
            $event = $this->eventRepository->findOneByIdOrFail($eventId);
            $events->add($event);
        }

        $eventDiscount = (new EventDiscount())
            ->setCode($dto->code)
            ->setDescription($dto->description)
            ->setDiscountType($discountType)
            ->setDiscountValue($dto->discountValue)
            ->setMaximumUses($dto->maximumUses)
            ->setMinimumPurchaseAmount($dto->minimumPurchaseAmount)
            ->setStartDate($dto->startDate)
            ->setEndDate($dto->endDate)
            ->setIsActive($dto->isActive);

        foreach ($events as $event) {
            $eventEventDiscount = (new EventEventDiscount())
                ->setEventDiscount($eventDiscount)
                ->setEvent($event);

            $eventDiscount->addEventEventDiscount(
                $eventEventDiscount
            );

            $this->entityManager->persist($eventEventDiscount);
        }

        $this->eventDiscountRepository->save($eventDiscount, true);

        $eventDiscountUsage = $this->resolveEventDiscountUsage($eventDiscount);

        return GetEventDiscountResponseDTO::fromEntity(
            $eventDiscount,
            $eventDiscountUsage,
        );
    }
}
