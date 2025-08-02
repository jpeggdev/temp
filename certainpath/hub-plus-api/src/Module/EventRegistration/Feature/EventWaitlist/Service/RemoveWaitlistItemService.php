<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventWaitlist\Service;

use App\Entity\EventEnrollmentWaitlist;
use App\Entity\EventSession;
use App\Module\EventRegistration\Feature\EventWaitlist\DTO\Request\RemoveWaitlistItemRequestDTO;
use App\Module\EventRegistration\Feature\EventWaitlist\DTO\Response\RemoveWaitlistItemResponseDTO;
use App\Module\EventRegistration\Feature\EventWaitlist\Exception\WaitlistItemNotFoundException;
use App\Repository\EventEnrollmentWaitlistRepository;
use Doctrine\ORM\EntityManagerInterface;

readonly class RemoveWaitlistItemService
{
    public function __construct(
        private EventEnrollmentWaitlistRepository $waitlistRepository,
        private EntityManagerInterface $em,
    ) {
    }

    public function removeItem(
        EventSession $session,
        RemoveWaitlistItemRequestDTO $dto,
    ): RemoveWaitlistItemResponseDTO {
        $item = $this->waitlistRepository->findOneByIdAndSession(
            $dto->eventWaitlistId,
            $session
        );
        if (!$item instanceof EventEnrollmentWaitlist) {
            throw new WaitlistItemNotFoundException(sprintf('Waitlist item %d not found in session %d.', $dto->eventWaitlistId, $session->getId()));
        }

        $this->em->remove($item);
        $this->reIndexWaitlist($session);

        $this->em->flush();

        return RemoveWaitlistItemResponseDTO::success($dto->eventWaitlistId);
    }

    private function reIndexWaitlist(EventSession $session): void
    {
        $activeItems = $this->waitlistRepository->findAllBySessionOrderByPosition($session);

        foreach ($activeItems as $index => $waitlistItem) {
            $waitlistItem->setWaitlistPosition($index + 1);
        }
    }
}
