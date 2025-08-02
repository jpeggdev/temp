<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventWaitlist\Service;

use App\Entity\EventEnrollmentWaitlist;
use App\Entity\EventSession;
use App\Module\EventRegistration\Feature\EventWaitlist\DTO\Request\UpdateWaitlistPositionRequestDTO;
use App\Module\EventRegistration\Feature\EventWaitlist\Exception\WaitlistItemNotFoundException;
use App\Repository\EventEnrollmentWaitlistRepository;
use Doctrine\ORM\EntityManagerInterface;

readonly class UpdateWaitlistPositionService
{
    public function __construct(
        private EventEnrollmentWaitlistRepository $waitlistRepository,
        private EntityManagerInterface $em,
    ) {
    }

    public function updatePosition(
        EventSession $session,
        UpdateWaitlistPositionRequestDTO $dto,
    ): void {
        $allItems = $this->waitlistRepository->findAllBySessionOrderByPosition($session);

        if (empty($allItems)) {
            return;
        }

        $targetItem = $this->waitlistRepository->findOneByIdAndSession(
            $dto->eventWaitlistId,
            $session
        );

        if (!$targetItem) {
            throw new WaitlistItemNotFoundException(sprintf('Waitlist item %d not found in session %d', $dto->eventWaitlistId, $session->getId()));
        }

        $filtered = array_filter(
            $allItems,
            fn (EventEnrollmentWaitlist $w) => $w->getId() !== $targetItem->getId()
        );

        $filtered = array_values($filtered);

        $count = count($filtered);
        $safePosition = min(max($dto->newPosition, 1), $count + 1);

        array_splice($filtered, $safePosition - 1, 0, [$targetItem]);

        foreach ($filtered as $index => $item) {
            $item->setWaitlistPosition($index + 1);
        }

        $this->em->flush();
    }
}
