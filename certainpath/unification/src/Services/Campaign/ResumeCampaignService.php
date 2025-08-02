<?php

namespace App\Services\Campaign;

use App\Entity\Campaign;
use App\Entity\CampaignEvent;
use App\Entity\CampaignIterationStatus;
use App\Exceptions\DomainException\Campaign\CampaignResumeFailedException;
use App\Exceptions\NotFoundException\CampaignEventNotFoundException;
use App\Message\CampaignIterations\ResumeCampaignIterationsMessage;
use App\Repository\CampaignEventRepository;
use App\Repository\CampaignIterationRepository;
use App\Services\CampaignEventService;
use Doctrine\Common\Collections\ArrayCollection;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

readonly class ResumeCampaignService
{
    public function __construct(
        private LoggerInterface $logger,
        private MessageBusInterface $messageBus,
        private CampaignEventService $campaignEventService,
        private CampaignEventRepository $campaignEventRepository,
        private CampaignIterationRepository $campaignIterationRepository,
    ) {
    }

    /**
     * @throws CampaignEventNotFoundException
     * @throws CampaignResumeFailedException
     */
    public function resume(Campaign $campaign): Campaign
    {
        $pausedIterations = $this->campaignIterationRepository->findAllByCampaignIdAndStatus(
            $campaign->getId(),
            [CampaignIterationStatus::STATUS_PAUSED]
        );
        $campaignPausedEvent = $this->campaignEventRepository->findLastByCampaignIdOrFail(
            $campaign->getId()
        );

        try {
            $this->checkCampaignCanBeResumed(
                $campaign,
                $pausedIterations,
                $campaignPausedEvent
            );

            $this->campaignEventService->createCampaignResumingEvent($campaign);
            $this->dispatchResumeCampaignIterationsMessage($campaign, $campaignPausedEvent);

            return $campaign;
        } catch (Exception $e) {
            $this->handleError($e);
            throw $e;
        }
    }

    /**
     * @throws CampaignResumeFailedException
     */
    private function checkCampaignCanBeResumed(
        Campaign $campaign,
        ArrayCollection $pausedIterations,
        CampaignEvent $campaignEvent
    ): void {
        $campaignId = $campaign->getId();

        if (!$campaign->isPaused()) {
            $message = sprintf('Campaign with id %d is not paused.', $campaignId);
            throw new CampaignResumeFailedException($message);
        }

        if ($pausedIterations->isEmpty()) {
            $message = sprintf("Campaign with id %d doesn't have paused iterations.", $campaignId);
            throw new CampaignResumeFailedException($message);
        }

        if ($campaignEvent->getEventStatus()->isResuming()) {
            $message = sprintf("Campaign with id %d is already being resumed.", $campaignId);
            throw new CampaignResumeFailedException($message);
        }

        if (!$campaignEvent->getEventStatus()->isPaused()) {
            $message = sprintf("Campaign with id %d does not have an associated paused event.", $campaignId);
            throw new CampaignResumeFailedException($message);
        }
    }

    private function dispatchResumeCampaignIterationsMessage(
        Campaign $campaign,
        CampaignEvent $campaignPausedEvent
    ): void {
        $message = new ResumeCampaignIterationsMessage(
            $campaign->getId(),
            $campaignPausedEvent->getId()
        );

        $this->messageBus->dispatch($message);
    }

    private function handleError(Exception $e): void
    {
        $this->logger->error($e->getMessage());
    }
}
