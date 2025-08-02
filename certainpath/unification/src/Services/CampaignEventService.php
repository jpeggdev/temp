<?php

namespace App\Services;

use App\DTO\Request\Campaign\CreateCampaignDTO;
use App\Entity\Campaign;
use App\Entity\CampaignEvent;
use App\Entity\EventStatus;
use App\Exceptions\NotFoundException\CampaignNotFoundException;
use App\Repository\CampaignEventRepository;
use App\Repository\CampaignRepository;
use App\Repository\EventStatusRepository;
use Doctrine\Common\Collections\ArrayCollection;

class CampaignEventService
{
    public function __construct(
        private readonly CampaignRepository $campaignRepository,
        private readonly EventStatusRepository $eventStatusRepository,
        private readonly CampaignEventRepository $campaignEventRepository,
    ) {
    }

    public function createCampaignPendingEvent(
        CreateCampaignDTO $dto,
        Campaign $campaign
    ): void {
        $statusPending = $this->eventStatusRepository->findByEventCampaignStatus(
            EventStatus::pending()
        );

        $pendingEvent = (new CampaignEvent())
            ->setEventStatus($statusPending)
            ->setCampaign($campaign)
            ->setCampaignIdentifier($dto->getIdentifier());

        $this->campaignEventRepository->addEvent(
            $pendingEvent
        );
    }

    public function createCampaignProcessingEvent(
        CreateCampaignDTO $dto,
        Campaign $campaign
    ): void {
        $statusProcessing = $this->eventStatusRepository->findByEventCampaignStatus(
            EventStatus::processing()
        );

        $processingEvent = (new CampaignEvent())
            ->setEventStatus($statusProcessing)
            ->setCampaign($campaign)
            ->setCampaignIdentifier($dto->getIdentifier());

        $this->campaignEventRepository->addEvent(
            $processingEvent
        );
    }

    public function createCampaignCreatedEvent(
        CreateCampaignDTO $dto,
        Campaign $campaign
    ): void {
        $statusCreated = $this->eventStatusRepository->findByEventCampaignStatus(
            EventStatus::created()
        );

        $createdEvent = (new CampaignEvent())
            ->setEventStatus($statusCreated)
            ->setCampaign($campaign)
            ->setCampaignIdentifier($dto->getIdentifier());

        $this->campaignEventRepository->addEvent(
            $createdEvent
        );
    }

    public function createCampaignActiveEvent(Campaign $campaign): void
    {
        $statusActive = $this->eventStatusRepository->findByEventCampaignStatus(
            EventStatus::active()
        );

        $activeEvent = (new CampaignEvent())
            ->setEventStatus($statusActive)
            ->setCampaign($campaign)
            ->setCampaignIdentifier('wildcard');

        $this->campaignEventRepository->addEvent(
            $activeEvent
        );
    }

    public function createCampaignFailedEvent(
        CreateCampaignDTO $dto,
        Campaign $campaign,
        ?string $errorMessage = null
    ): void {
        $statusFailed = $this->eventStatusRepository->findByEventCampaignStatus(
            EventStatus::failed()
        );

        $failedEvent = (new CampaignEvent())
            ->setEventStatus($statusFailed)
            ->setCampaign($campaign)
            ->setCampaignIdentifier($dto->getIdentifier())
            ->setErrorMessage($errorMessage);

        $this->campaignEventRepository->addEvent(
            $failedEvent
        );
    }

    public function createCampaignPausedEvent(Campaign $campaign): void
    {
        $statusPaused = $this->eventStatusRepository->findByEventCampaignStatus(
            EventStatus::paused()
        );

        $pausedEvent = (new CampaignEvent())
            ->setEventStatus($statusPaused)
            ->setCampaign($campaign)
            ->setCampaignIdentifier('wildcard');

        $this->campaignEventRepository->addEvent(
            $pausedEvent
        );
    }

    public function createCampaignResumingEvent(Campaign $campaign): void
    {
        $statusResuming = $this->eventStatusRepository->findByEventCampaignStatus(
            EventStatus::resuming()
        );

        $resumingEvent = (new CampaignEvent())
            ->setEventStatus($statusResuming)
            ->setCampaign($campaign)
            ->setCampaignIdentifier('wildcard');

        $this->campaignEventRepository->addEvent(
            $resumingEvent
        );
    }

    public function isCampaignPending(CreateCampaignDTO $campaignDto): bool
    {
        $lastEvent = $this->campaignEventRepository->findLastByCampaignIdentified(
            $campaignDto->getIdentifier()
        );

        if ($lastEvent === null) {
            return false;
        }

        return $lastEvent->getEventStatus()->equals(
            EventStatus::pending()
        );
    }

    public function isCampaignFailed(CreateCampaignDTO $campaignDto): bool
    {
        $lastEvent = $this->campaignEventRepository->findLastByCampaignIdentified(
            $campaignDto->getIdentifier()
        );

        if ($lastEvent === null) {
            return false;
        }

        return $lastEvent->getEventStatus()->equals(
            EventStatus::failed()
        );
    }

    public function isCampaignCreated(CreateCampaignDTO $campaignDto): bool
    {
        $lastEvent = $this->campaignEventRepository->findLastByCampaignIdentified(
            $campaignDto->getIdentifier()
        );

        if ($lastEvent === null) {
            return false;
        }

        return $lastEvent->getEventStatus()->equals(
            EventStatus::created()
        );
    }

    /**
     * @throws CampaignNotFoundException
     */
    public function getCampaignEvents(int $id): ArrayCollection
    {
        $campaign = $this->campaignRepository->findOneByIdOrFail($id);
        return $this->campaignEventRepository->findAllByCampaignId($campaign->getId());
    }
}
