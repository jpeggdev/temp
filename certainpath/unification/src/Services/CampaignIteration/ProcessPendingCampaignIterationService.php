<?php

namespace App\Services\CampaignIteration;

use App\DTO\Domain\ProspectFilterRulesDTO;
use App\Entity\Campaign;
use App\Entity\CampaignIteration;
use App\Entity\CampaignIterationStatus;
use App\Exceptions\DomainException\CampaignIteration\CampaignIterationCannotBeProcessedException;
use App\Exceptions\NotFoundException\BatchNotFoundException;
use App\Exceptions\NotFoundException\BatchStatusNotFoundException;
use App\Exceptions\NotFoundException\CampaignIterationStatusNotFoundException;
use App\Exceptions\NotFoundException\CampaignNotFoundException;
use App\Exceptions\NotFoundException\CompanyNotFoundException;
use App\Exceptions\NotFoundException\MailPackageNotFoundException;
use App\Exceptions\NotFoundException\ProspectFilterRuleNotFoundException;
use App\Repository\CampaignIterationRepository;
use App\Repository\CampaignIterationStatusRepository;
use App\Repository\ProspectRepository;
use App\Services\BatchService;
use App\Services\CampaignIterationWeek\CreateCampaignIterationWeekService;
use Carbon\Carbon;
use Exception;

readonly class ProcessPendingCampaignIterationService extends BaseCampaignIterationService
{
    public function __construct(
        ProspectRepository $prospectRepository,
        CampaignIterationRepository $campaignIterationRepository,
        CampaignIterationStatusRepository $campaignIterationStatusRepository,
        private BatchService $batchService,
        private CreateCampaignIterationWeekService $campaignIterationWeekService,
    ) {
        parent::__construct(
            $prospectRepository,
            $campaignIterationRepository,
            $campaignIterationStatusRepository,
        );
    }

    /**
     * @throws Exception
     * @throws \JsonException
     * @throws BatchNotFoundException
     * @throws CompanyNotFoundException
     * @throws CampaignNotFoundException
     * @throws BatchStatusNotFoundException
     * @throws MailPackageNotFoundException
     * @throws CampaignIterationStatusNotFoundException
     * @throws CampaignIterationCannotBeProcessedException
     * @throws ProspectFilterRuleNotFoundException
     */
    public function processPendingCampaignIteration(CampaignIteration $campaignIteration): void
    {
        $campaign = $campaignIteration->getCampaign();
        if (!$campaign) {
            throw new CampaignNotFoundException();
        }

        $company = $campaign->getCompany();
        if (!$company) {
            throw new CompanyNotFoundException();
        }

        $this->checkCampaignIterationCanBeProcessed($campaign, $campaignIteration);

        $dateToday = Carbon::today();
        $prospectFilterRulesDTO = ProspectFilterRulesDTO::createFromCampaignObject($campaign);
        $prospects = $this->prospectRepository->fetchAllByProspectFilterRulesDTO($prospectFilterRulesDTO);
        $campaignIterationStatusActive = $this->campaignIterationStatusRepository->findOneByNameOrFail(
            CampaignIterationStatus::STATUS_ACTIVE
        );
        $campaignIterationStatusCompleted = $this->campaignIterationStatusRepository->findOneByNameOrFail(
            CampaignIterationStatus::STATUS_COMPLETED
        );

        foreach ($campaignIteration->getCampaignIterationWeeks() as $weekIndex => $campaignIterationWeek) {
            if (!$campaignIterationWeek->isMailingDropWeek()) {
                continue;
            }

            $campaignIterationWeekNumber = $weekIndex + 1;

            $batchProspects = $this->campaignIterationWeekService->getBatchProspectsSlice(
                $prospects,
                $campaignIterationWeekNumber,
                $campaign->getMailingDropWeeks(),
            );

            $this->batchService->createBatch(
                $campaign,
                $batchProspects,
                $campaignIteration,
                $campaignIterationWeek
            );

            $status = $campaignIteration->getEndDate() < $dateToday
                ? $campaignIterationStatusCompleted
                : $campaignIterationStatusActive;

            $campaignIteration->setCampaignIterationStatus($status);

            $this->campaignIterationRepository->saveCampaignIteration($campaignIteration);
        }
    }

    /**
     * @throws CampaignIterationCannotBeProcessedException
     */
    private function checkCampaignIterationCanBeProcessed(
        Campaign $campaign,
        CampaignIteration $campaignIteration,
    ): void {
        if (
            $campaign->isCompleted() ||
            $campaignIteration->isCompleted() ||
            !$campaign->getMailingFrequencyWeeks() ||
            empty($campaign->getMailingDropWeeks())
        ) {
            throw new CampaignIterationCannotBeProcessedException();
        }
    }
}
