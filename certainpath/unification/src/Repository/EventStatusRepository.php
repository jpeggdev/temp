<?php

namespace App\Repository;

use App\Entity\EventStatus;
use Doctrine\Persistence\ManagerRegistry;

class EventStatusRepository extends AbstractRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EventStatus::class);
    }

    public function saveCampaignEventStatus(EventStatus $statusCreated): void
    {
        $this->save($statusCreated);
    }

    public function findByEventCampaignStatus(EventStatus $status): EventStatus
    {
        return $this->findOneBy(['name' => $status->getName()]);
    }

    public function findOneName(string $name)
    {
        return $this->findOneBy(['name' => $name]);
    }
}
