<?php

declare(strict_types=1);

namespace App\Service\Event;

use App\Entity\Employee;
use App\Entity\Event;
use App\Entity\EventFavorite;
use App\Repository\EventFavoriteRepository;
use Doctrine\ORM\EntityManagerInterface;

readonly class FavoriteEventService
{
    public function __construct(
        private EventFavoriteRepository $eventFavoriteRepository,
        private EntityManagerInterface $em,
    ) {
    }

    public function toggleFavorite(Event $event, Employee $employee): bool
    {
        $existingFavorite = $this->eventFavoriteRepository->findOneBy([
            'event' => $event,
            'employee' => $employee,
        ]);

        if ($existingFavorite) {
            $this->em->remove($existingFavorite);
            $this->em->flush();

            return false;
        }

        $favorite = new EventFavorite();
        $favorite->setEvent($event);
        $favorite->setEmployee($employee);

        $this->em->persist($favorite);
        $this->em->flush();

        return true;
    }
}
