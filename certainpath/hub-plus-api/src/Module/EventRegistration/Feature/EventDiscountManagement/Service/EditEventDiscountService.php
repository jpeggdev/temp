<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventDiscountManagement\Service;

use App\Entity\Event;
use App\Entity\EventEventDiscount;
use App\Module\EventRegistration\Feature\EventDiscountManagement\DTO\Request\CreateUpdateEventDiscountDTO;
use App\Module\EventRegistration\Feature\EventDiscountManagement\DTO\Response\GetEventDiscountResponseDTO;
use App\Repository\DiscountTypeRepository;
use App\Repository\EventDiscountRepository;
use App\Repository\EventRepository\EventRepository;
use App\Repository\InvoiceLineItemRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;

readonly class EditEventDiscountService extends BaseEventDiscountService
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

    /**
     * @throws \Exception
     */
    public function editDiscount(
        int $eventDiscountId,
        CreateUpdateEventDiscountDTO $dto,
    ): GetEventDiscountResponseDTO {
        $eventDiscount = $this->eventDiscountRepository->findOneByIdOrFail($eventDiscountId);
        $discountType = $this->discountTypeRepository->findOneByIdOrFail($dto->discountTypeId);
        $events = $this->prepareEvents($dto->eventIds);

        $this->entityManager->beginTransaction();

        try {
            $eventDiscount
                ->setCode($dto->code)
                ->setDescription($dto->description)
                ->setDiscountType($discountType)
                ->setDiscountValue($dto->discountValue)
                ->setMinimumPurchaseAmount($dto->minimumPurchaseAmount)
                ->setStartDate($dto->startDate)
                ->setEndDate($dto->endDate)
                ->setIsActive($dto->isActive);

            foreach ($eventDiscount->getEventEventDiscounts() as $eventEventDiscount) {
                $this->entityManager->remove($eventEventDiscount);
            }

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
            $this->entityManager->commit();
        } catch (\Exception $e) {
            $this->entityManager->rollback();
            throw $e;
        }

        $eventDiscountUsage = $this->resolveEventDiscountUsage($eventDiscount);

        return GetEventDiscountResponseDTO::fromEntity(
            $eventDiscount,
            $eventDiscountUsage
        );
    }

    /**
     * @return ArrayCollection<int, Event>
     */
    private function prepareEvents(array $eventIds): ArrayCollection
    {
        $events = new ArrayCollection();
        foreach ($eventIds as $eventId) {
            $event = $this->eventRepository->findOneByIdOrFail($eventId);
            $events->add($event);
        }

        return $events;
    }
}
