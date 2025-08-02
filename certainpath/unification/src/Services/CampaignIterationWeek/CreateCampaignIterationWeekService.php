<?php

namespace App\Services\CampaignIterationWeek;

use App\Entity\Campaign;
use App\Entity\CampaignIteration;
use App\Entity\CampaignIterationWeek;
use App\Exceptions\NotFoundException\BatchNotFoundException;
use App\Exceptions\NotFoundException\BatchStatusNotFoundException;
use App\Exceptions\NotFoundException\CampaignIterationWeekNotFoundException;
use App\Exceptions\NotFoundException\MailPackageNotFoundException;
use App\Repository\CampaignIterationWeekRepository;
use App\Services\BatchService;
use App\ValueObjects\CampaignIterationWeekObject;
use Carbon\Carbon;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Exception;

readonly class CreateCampaignIterationWeekService extends BaseCampaignIterationWeekService
{
    public function __construct(
        private BatchService $batchService,
        private CampaignIterationWeekRepository $campaignIterationWeekRepository,
    ) {
    }

    /**
     * @throws Exception
     * @throws BatchNotFoundException
     * @throws BatchStatusNotFoundException
     * @throws MailPackageNotFoundException
     * @throws CampaignIterationWeekNotFoundException
     */
    public function createCampaignIterationWeeks(
        Campaign $campaign,
        CampaignIteration $campaignIteration,
        Collection $prospects,
        Carbon $iterationStartDate
    ): void {
        $campaignEndDate = Carbon::instance($campaign->getEndDate());
        $iterationNumber = $campaignIteration->getIterationNumber();
        $mailingFrequencyWeeks = $campaign->getMailingFrequencyWeeks();
        $mailingDropWeeks = $campaign->getMailingDropWeeks();
        $campaignIterationWeekObjects = new ArrayCollection();

        for ($week = 1; $week <= $mailingFrequencyWeeks; $week++) {
            $weekStartDate = $iterationStartDate->copy();
            $weekEndDate = $iterationStartDate->copy()->endOfWeek();
            $weekNumber = $weekStartDate->weekOfYear;

            if ($weekEndDate->greaterThan($campaignEndDate)) {
                $weekEndDate = $campaignEndDate->copy();
            }

            $isFirstIteration = $iterationNumber === 1;
            $isMailingDropWeek = in_array($week, $mailingDropWeeks, true);

            if ($isFirstIteration) {
                $batchProspects = new ArrayCollection();

                if ($isMailingDropWeek) {
                    $batchProspects = $this->getBatchProspectsSlice(
                        $prospects,
                        $week,
                        $mailingDropWeeks,
                    );
                }

                $this->createCampaignIterationWeek(
                    $campaign,
                    $campaignIteration,
                    $weekNumber,
                    $isMailingDropWeek,
                    $weekStartDate,
                    $weekEndDate,
                    $batchProspects
                );
            } else {
                $campaignIterationWeekObject = (new CampaignIterationWeekObject())->fromArray([
                    'campaign_iteration_id' => $campaignIteration->getId(),
                    'week_number' => $weekNumber,
                    'is_mailing_drop_week' => $isMailingDropWeek,
                    'start_date' => $weekStartDate->format('Y-m-d'),
                    'end_date' => $weekEndDate->format('Y-m-d'),
                ]);

                $campaignIterationWeekObjects->add($campaignIterationWeekObject);
            }

            if ($weekEndDate->equalTo($campaignEndDate)) {
                break;
            }

            $iterationStartDate->startOfWeek()->addWeek();
        }

        if (!$campaignIterationWeekObjects->isEmpty()) {
            $this->campaignIterationWeekRepository->bulkInsertCampaignIterationWeeks(
                $campaignIterationWeekObjects
            );
        }
    }

    /**
     * @throws Exception
     * @throws BatchNotFoundException
     * @throws BatchStatusNotFoundException
     * @throws MailPackageNotFoundException
     * @throws CampaignIterationWeekNotFoundException
     */
    private function createCampaignIterationWeek(
        Campaign $campaign,
        CampaignIteration $campaignIteration,
        int $weekNumber,
        bool $isMailingDropWeek,
        Carbon $startDate,
        Carbon $endDate,
        Collection $batchProspects
    ): void {
        $campaignIterationWeek = $this->saveCampaignIterationWeek(
            $campaignIteration,
            $weekNumber,
            $isMailingDropWeek,
            $startDate,
            $endDate
        );

        if ($isMailingDropWeek) {
            $this->batchService->createBatch(
                $campaign,
                $batchProspects,
                $campaignIteration,
                $campaignIterationWeek,
            );
        }
    }

    /**
     * @throws Exception
     * @throws CampaignIterationWeekNotFoundException
     */
    private function saveCampaignIterationWeek(
        CampaignIteration $campaignIteration,
        int $weekNumber,
        bool $isMailingDropWeek,
        Carbon $startDate,
        Carbon $endDate
    ): CampaignIterationWeek {
        $campaignIterationWeekObject = (new CampaignIterationWeekObject())->fromArray([
            'campaign_iteration_id' => $campaignIteration->getId(),
            'week_number' => $weekNumber,
            'is_mailing_drop_week' => $isMailingDropWeek,
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d'),
        ]);

        $lastInsertedId = $this->campaignIterationWeekRepository->saveCampaignIterationWeekDBAL(
            $campaignIterationWeekObject
        );

        return $this->campaignIterationWeekRepository->findOneByIdOrFail($lastInsertedId);
    }
}
