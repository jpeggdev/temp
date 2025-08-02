<?php

namespace App\Services\CampaignIterationWeek;

use App\Entity\Campaign;
use App\Entity\CampaignEvent;
use App\Entity\CampaignIteration;
use App\Entity\CampaignIterationWeek;
use App\Exceptions\InvalidArgumentException\InvalidCampaignPausedDate;
use App\Exceptions\NotFoundException\BatchNotFoundException;
use App\Exceptions\NotFoundException\BatchStatusNotFoundException;
use App\Exceptions\NotFoundException\MailPackageNotFoundException;
use App\Repository\CampaignIterationWeekRepository;
use App\Services\BatchService;
use Carbon\Carbon;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\DBAL\Exception;

readonly class ResumeCampaignIterationWeekService extends BaseCampaignIterationWeekService
{
    public function __construct(
        private BatchService $batchService,
        private CampaignIterationWeekRepository $campaignIterationWeekRepository,
    ) {
    }

    /**
     * @throws Exception
     * @throws BatchNotFoundException
     * @throws MailPackageNotFoundException
     * @throws BatchStatusNotFoundException
     */
    private function resumeCampaignIterationWeek(
        Campaign $campaign,
        CampaignIteration $campaignIteration,
        CampaignIterationWeek $campaignIterationWeek,
        CampaignEvent $campaignEventPaused,
        ArrayCollection $prospects,
        int $campaignIterationWeekNumber
    ): void {
        if ($campaignIterationWeek->getEndDate() < $campaignEventPaused->getCreatedAt()) {
            return;
        }

        $shiftedWeeksCount = $this->calculateShiftedWeeksCount($campaignEventPaused);
        $originalWeekStart = Carbon::instance($campaignIterationWeek->getStartDate());
        $newWeekStart = $originalWeekStart->addWeeks($shiftedWeeksCount)->startOfWeek();
        $newWeekEnd = $newWeekStart->copy()->endOfWeek();
        $newCalendarWeekNumber = $newWeekStart->weekOfYear;

        $campaignIterationWeek->setStartDate($newWeekStart);
        $campaignIterationWeek->setEndDate($newWeekEnd);
        $campaignIterationWeek->setWeekNumber($newCalendarWeekNumber);

        $this->campaignIterationWeekRepository->saveCampaignIterationWeek($campaignIterationWeek);

        if (!$campaignIterationWeek->isMailingDropWeek()) {
            return;
        }

        $batch = $campaignIterationWeek->getBatch();
        if (!$batch) {
            return;
        }

        $batchProspects = $this->getBatchProspectsSlice(
            $prospects,
            $campaignIterationWeekNumber,
            $campaign->getMailingDropWeeks()
        );

        $this->batchService->resumeBatch(
            $batch,
            $campaign,
            $batchProspects,
            $campaignIteration,
            $campaignIterationWeek
        );
    }

    /**
     * @throws Exception
     * @throws BatchNotFoundException
     * @throws MailPackageNotFoundException
     * @throws BatchStatusNotFoundException
     */
    public function resumeCampaignIterationWeeks(
        Campaign $campaign,
        CampaignEvent $campaignPausedEvent,
        CampaignIteration $campaignIteration,
        ArrayCollection $campaignIterationWeeks,
        ArrayCollection $prospects,
    ): void {
        foreach ($campaignIterationWeeks as $campaignIterationWeek) {
            $campaignIterationWeekNumber = $this->calculateCampaignIterationWeekNumber(
                $campaignIterationWeek,
                $campaignIterationWeeks
            );

            if ($campaignIterationWeekNumber === false) {
                continue;
            }

            $this->resumeCampaignIterationWeek(
                $campaign,
                $campaignIteration,
                $campaignIterationWeek,
                $campaignPausedEvent,
                $prospects,
                $campaignIterationWeekNumber
            );
        }
    }

    private function calculateShiftedWeeksCount(CampaignEvent $campaignPausedEvent): int
    {
        $dateToday = Carbon::now();
        $dateCampaignWasPaused = Carbon::instance($campaignPausedEvent->getCreatedAt());

        if ($dateCampaignWasPaused->isFuture()) {
            throw new InvalidCampaignPausedDate($dateCampaignWasPaused->format('Y-m-d'));
        }

        if ($dateToday->startOfWeek()->eq($dateCampaignWasPaused->startOfWeek())) {
            return 0;
        }

        $weeksDiff = $dateCampaignWasPaused->startOfWeek()->diffInWeeks($dateToday->startOfWeek());

        if ($weeksDiff <= 0) {
            return 0;
        }

        return (int) $weeksDiff;
    }

    private function calculateCampaignIterationWeekNumber(
        CampaignIterationWeek $campaignIterationWeek,
        ArrayCollection $campaignIterationWeeks
    ): int|bool {
        return $campaignIterationWeeks->indexOf($campaignIterationWeek) + 1;
    }
}
