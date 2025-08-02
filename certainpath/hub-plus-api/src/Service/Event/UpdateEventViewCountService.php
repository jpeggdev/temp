<?php

declare(strict_types=1);

namespace App\Service\Event;

use App\Entity\Event;
use Doctrine\ORM\EntityManagerInterface;

readonly class UpdateEventViewCountService
{
    public function __construct(
        private EntityManagerInterface $em,
    ) {
    }

    public function incrementViewCount(Event $event): void
    {
        $event->setViewCount($event->getViewCount() + 1);
        $this->em->flush();
    }
}
