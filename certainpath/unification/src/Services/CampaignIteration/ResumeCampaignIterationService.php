<?php

namespace App\Services\CampaignIteration;

use App\Entity\Campaign;
use App\Entity\CampaignEvent;
use App\Entity\CampaignIteration;
use App\Entity\CampaignIterationStatus;
use App\Exceptions\DomainException\Campaign\CampaignResumeFailedException;
use App\Exceptions\NotFoundException\BatchNotFoundException;
use App\Exceptions\NotFoundException\BatchStatusNotFoundException;
use App\Exceptions\NotFoundException\CampaignIterationNotFoundException;
use App\Exceptions\NotFoundException\CampaignIterationStatusNotFoundException;
use App\Exceptions\NotFoundException\CampaignStatusNotFoundException;
use App\Exceptions\NotFoundException\MailPackageNotFoundException;
use App\Exceptions\NotFoundException\ProspectFilterRuleNotFoundException;
use App\Repository\CampaignIterationRepository;
use App\Repository\CampaignIterationStatusRepository;
use App\Repository\CampaignIterationWeekRepository;
use App\Repository\CampaignRepository;
use App\Repository\CampaignStatusRepository;
use App\Repository\ProspectRepository;
use App\Services\CampaignEventService;
use App\Services\CampaignIterationWeek\ResumeCampaignIterationWeekService;
use Doctrine\Common\Collections\ArrayCollection;
use Exception;
use Psr\Log\LoggerInterface;

readonly class ResumeCampaignIterationService extends BaseCampaignIterationService
{
    public function __construct(
        ProspectRepository $prospectRepository,
        CampaignIterationRepository $campaignIterationRepository,
        CampaignIterationStatusRepository $campaignIterationStatusRepository,
        private LoggerInterface $logger,
        private CampaignRepository $campaignRepository,
        private CampaignStatusRepository $campaignStatusRepository,
        private CampaignIterationWeekRepository $campaignIterationWeekRepository,
        private ResumeCampaignIterationWeekService $resumeCampaignIterationWeekService,
        private CampaignEventService $campaignEventService,
    ) {
        parent::__construct(
            $prospectRepository,
            $campaignIterationRepository,
            $campaignIterationStatusRepository
        );
    }

    /**
     * @throws BatchNotFoundException
     * @throws \Doctrine\DBAL\Exception
     * @throws CampaignResumeFailedException
     * @throws BatchStatusNotFoundException
     * @throws MailPackageNotFoundException
     */
    private function resumeCampaignIteration(
        Campaign $campaign,
        CampaignEvent $campaignPausedEvent,
        CampaignIteration $pausedCampaignIteration,
        CampaignIterationStatus $campaignIterationStatus,
        ArrayCollection $prospects,
        bool $shouldRecalculateStartDate = false
    ): void {
        $campaignIterationWeeks = $this->campaignIterationWeekRepository->findAllByCampaignIterationId(
            $pausedCampaignIteration->getId()
        );

        $this->checkCampaignIterationCanBeResumed(
            $pausedCampaignIteration,
            $campaignPausedEvent,
            $campaignIterationWeeks
        );

        $this->resumeCampaignIterationWeekService->resumeCampaignIterationWeeks(
            $campaign,
            $campaignPausedEvent,
            $pausedCampaignIteration,
            $campaignIterationWeeks,
            $prospects
        );

        $this->refreshCampaignIterationStartAndEndDate(
            $pausedCampaignIteration,
            $shouldRecalculateStartDate
        );

        $pausedCampaignIteration->setCampaignIterationStatus($campaignIterationStatus);
        $this->campaignIterationRepository->saveCampaignIteration($pausedCampaignIteration);
    }

    /**
     * @throws \JsonException
     * @throws BatchNotFoundException
     * @throws \Doctrine\DBAL\Exception
     * @throws BatchStatusNotFoundException
     * @throws CampaignResumeFailedException
     * @throws MailPackageNotFoundException
     * @throws CampaignStatusNotFoundException
     * @throws CampaignIterationNotFoundException
     * @throws ProspectFilterRuleNotFoundException
     * @throws CampaignIterationStatusNotFoundException
     */
    public function resumeCampaignIterations(
        Campaign $campaign,
        CampaignEvent $campaignEventPaused
    ): void {
        $campaignStatusActive = $this->campaignStatusRepository->findOneByNameOrFail(
            CampaignIterationStatus::STATUS_ACTIVE
        );

        $campaignIterationStatusActive = $this->campaignIterationStatusRepository->findOneByNameOrFail(
            CampaignIterationStatus::STATUS_ACTIVE
        );

        $pausedIterations = $this->campaignIterationRepository->findAllByCampaignIdAndStatus(
            $campaign->getId(),
            [CampaignIterationStatus::STATUS_PAUSED]
        );

        try {
            $this->checkCampaignIterationsCanBeResumed(
                $campaign,
                $campaignEventPaused,
                $pausedIterations
            );

            $prospects = $this->getProspectsForProcessing($campaign);

            /** @var CampaignIteration $campaignIteration */
            foreach ($pausedIterations as $key => $campaignIteration) {
                $shouldRecalculateStartDate = $key !== 0;

                $this->resumeCampaignIteration(
                    $campaign,
                    $campaignEventPaused,
                    $campaignIteration,
                    $campaignIterationStatusActive,
                    $prospects,
                    $shouldRecalculateStartDate
                );
            }

            $campaign->setCampaignStatus($campaignStatusActive);
            $this->refreshCampaignEndDate($campaign);
            $this->campaignRepository->saveCampaign($campaign);
            $this->campaignEventService->createCampaignActiveEvent($campaign);
        } catch (Exception $e) {
            $this->handleError($e);
            throw $e;
        }
    }

    /**
     * @throws CampaignResumeFailedException
     */
    private function checkCampaignIterationCanBeResumed(
        CampaignIteration $campaignIteration,
        CampaignEvent $pausedEvent,
        ArrayCollection $campaignIterationWeeks,
    ): void {
        $campaignId = $campaignIteration->getCampaign()?->getId();
        $campaignIterationId = $campaignIteration->getId();

        if (!$campaignIteration->isPaused()) {
            $message = sprintf('Campaign iteration with ID %d is not paused.', $campaignIterationId);
            throw new CampaignResumeFailedException($message);
        }

        if ($campaignIterationWeeks->isEmpty()) {
            $message = sprintf("Campaign iteration with id %d has no associated weeks.", $campaignIterationId);
            throw new CampaignResumeFailedException($message);
        }

        if (!$pausedEvent->getEventStatus()->isPaused()) {
            $message = sprintf("Campaign with id %d does not have an associated paused event.", $campaignId);
            throw new CampaignResumeFailedException($message);
        }
    }

    /**
     * @throws CampaignResumeFailedException
     */
    private function checkCampaignIterationsCanBeResumed(
        Campaign $campaign,
        CampaignEvent $campaignPausedEvent,
        ArrayCollection $pausedIterations
    ): void {
        $campaignId = $campaign->getId();

        if ($pausedIterations->isEmpty()) {
            $message = sprintf("Campaign with id %d doesn't have paused iterations.", $campaignId);
            throw new CampaignResumeFailedException($message);
        }

        if (!$campaignPausedEvent->getEventStatus()->isPaused()) {
            $message = sprintf("Campaign with id %d does not have an associated paused event.", $campaignId);
            throw new CampaignResumeFailedException($message);
        }
    }

    private function refreshCampaignIterationStartAndEndDate(
        CampaignIteration $campaignIteration,
        bool $shouldRecalculateStartDate
    ): void {
        $campaignIterationWeeks = $this->campaignIterationWeekRepository->findAllByCampaignIterationId(
            $campaignIteration->getId()
        );

        if ($campaignIterationWeeks->isEmpty()) {
            return;
        }

        if ($shouldRecalculateStartDate) {
            $firstCampaignIterationWeek = $campaignIterationWeeks->first();
            $campaignIteration->setStartDate($firstCampaignIterationWeek->getStartDate());
        }

        $lastCampaignIterationWeek = $campaignIterationWeeks->last();
        $campaignIteration->setEndDate($lastCampaignIterationWeek->getEndDate());
    }

    /**
     * @throws CampaignIterationNotFoundException
     */
    private function refreshCampaignEndDate(Campaign $campaign): void
    {
        $latestCampaignIteration = $this->campaignIterationRepository->findLatestByCampaignIdOrFail(
            $campaign->getId()
        );

        $campaign->setEndDate($latestCampaignIteration->getEndDate());
    }

    private function handleError(Exception $e): void
    {
        $this->logger->error($e->getMessage());
    }
}
