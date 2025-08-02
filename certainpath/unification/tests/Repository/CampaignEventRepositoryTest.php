<?php

namespace App\Tests\Repository;

use App\Entity\CampaignEvent;
use App\Entity\EventStatus;
use App\Tests\FunctionalTestCase;

class CampaignEventRepositoryTest extends FunctionalTestCase
{
    public function testCampaignCreationEvent(): void
    {
        $campaignDto = $this->prepareCreateCampaignDTO();

        $this->initializeEventStatuses();

        $statusPending = $this->getEventStatusRepository()->findByEventCampaignStatus(
            EventStatus::pending()
        );

        $pendingEvent = (new CampaignEvent())
            ->setEventStatus($statusPending)
            ->setCampaignIdentifier($campaignDto->getIdentifier());


        self::assertTrue(
            $pendingEvent->getEventStatus()->equals($statusPending)
        );
        self::assertEquals(
            $campaignDto->getIdentifier(),
            $pendingEvent->getCampaignIdentifier()
        );

        $eventRepo = $this->getCampaignEventRepository();

        $eventRepo->addEvent($pendingEvent);
        self::assertTrue(
            $statusPending->equals(
                $eventRepo->findLastByCampaignIdentified($campaignDto->getIdentifier())->getEventStatus()
            )
        );

        $statusProcessing = $this->getEventStatusRepository()->findByEventCampaignStatus(
            EventStatus::processing()
        );

        $eventRepo->addEvent(
            (new CampaignEvent())
                ->setEventStatus($statusProcessing)
                ->setCampaignIdentifier($campaignDto->getIdentifier())
        );

        self::assertFalse(
            $statusPending->equals(
                $eventRepo->findLastByCampaignIdentified($campaignDto->getIdentifier())->getEventStatus()
            )
        );
        self::assertTrue(
            EventStatus::processing()->equals(
                $eventRepo->findLastByCampaignIdentified($campaignDto->getIdentifier())->getEventStatus()
            )
        );
    }
}
