<?php

namespace App\Services\CampaignIteration;

use App\Entity\Campaign;
use App\Entity\CampaignIteration;
use App\Entity\CampaignIterationStatus;
use App\Exceptions\NotFoundException\CampaignIterationStatusNotFoundException;
use App\Exceptions\OutputInterfaceMustBeSet;
use App\Repository\CampaignIterationRepository;
use App\Repository\CampaignRepository;
use App\Repository\CompanyRepository;
use App\Services\ApplicationSignalingService;
use Carbon\Carbon;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Console\Output\OutputInterface;

readonly class ProcessNextCampaignIterationService
{
    public function __construct(
        private ApplicationSignalingService $signaling,
        private CampaignRepository $campaignRepository,
        private CompanyRepository $companyRepository,
        private CampaignIterationRepository $campaignIterationRepository,
        private ProcessPendingCampaignIterationService $processPendingCampaignIterationService,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function setOutput(OutputInterface $output): void
    {
        $this->signaling->setOutput($output);
    }

    /**
     * @throws OutputInterfaceMustBeSet
     * @throws CampaignIterationStatusNotFoundException
     */
    public function processCampaignsIterations(
        ?int $campaignId,
        ?string $iterationStartDate,
    ): void {
        $campaignIterationStartDate = $iterationStartDate
            ? $this->parseDateString($iterationStartDate)
            : Carbon::now()->startOfDay();

        if (!$campaignIterationStartDate) {
            $message = sprintf(
                'Skipping Campaign %d. Failed to parse campaign iteration start date: %s.',
                $campaignId,
                $iterationStartDate
            );

            $this->signaling->console('<error>' . $message . '</error>');
        }

        if ($campaignId) {
            $campaign = $this->campaignRepository->findOneById($campaignId);

            if ($campaign) {
                $this->processIterations($campaign, $campaignIterationStartDate);
            }
        }

        $companies = $this->companyRepository->fetchAllActive();

        foreach ($companies as $company) {
            $campaigns = $this->campaignRepository->fetchAllActiveByCompanyId($company->getId());

            foreach ($campaigns as $campaign) {
                $this->processIterations($campaign, $campaignIterationStartDate);
            }
        }
    }

    /**
     * @throws OutputInterfaceMustBeSet
     * @throws CampaignIterationStatusNotFoundException
     */
    private function processIterations(
        Campaign $campaign,
        DateTime $campaignIterationStartDate,
    ): void {
        $campaignIterations = $this->campaignIterationRepository
            ->findCampaignIterationsByCampaignIdAndDateBeforeOrEqual(
                $campaign->getId(),
                $campaignIterationStartDate
            );

        /** @var CampaignIteration $campaignIteration */
        foreach ($campaignIterations as $campaignIteration) {
            if (!$this->canIterationBeProcessed($campaignIteration)) {
                continue;
            }

            $campaignIterationStatusName = $campaignIteration->getCampaignIterationStatus()?->getName();

            if (
                $campaignIterationStatusName === CampaignIterationStatus::STATUS_ACTIVE &&
                $campaignIteration->getEndDate() < $campaignIterationStartDate
            ) {
                $this->processPendingCampaignIterationService->completeCampaignIteration($campaignIteration);
            }

            if ($campaignIterationStatusName === CampaignIterationStatus::STATUS_PENDING) {
                $this->processPendingCampaignIteration($campaignIteration, $campaign);
            }
        }
    }

    /**
     * @throws OutputInterfaceMustBeSet
     */
    private function processPendingCampaignIteration(
        CampaignIteration $pendingIteration,
        Campaign $campaign
    ): void {
        try {
            $message = sprintf(
                'Processing campaign iteration %d for campaign %d',
                $pendingIteration->getIterationNumber(),
                $campaign->getId()
            );
            $this->signaling->console('<info>' . $message . '</info>');

            $this->processPendingCampaignIterationService->processPendingCampaignIteration($pendingIteration);

            $message = sprintf(
                'Processed campaign iteration %d for campaign %d',
                $pendingIteration->getIterationNumber(),
                $campaign->getId()
            );

            $this->signaling->info($message);
            $this->signaling->console('<info>' . $message . '</info>');
        } catch (Exception $exception) {
            $errorMessage = sprintf(
                'Failed to process campaign iteration %d for campaign %d. %s',
                $pendingIteration->getIterationNumber(),
                $campaign->getId(),
                $exception->getMessage()
            );

            $this->signaling->error($errorMessage);
            $this->signaling->console('<error>' . $errorMessage . '</error>');

            $this->entityManager->rollback();
        }
    }

    private function canIterationBeProcessed(CampaignIteration $campaignIteration): bool
    {
        $processableStatuses = [
            CampaignIterationStatus::STATUS_ACTIVE,
            CampaignIterationStatus::STATUS_PENDING,
        ];

        return in_array(
            $campaignIteration->getCampaignIterationStatus()?->getName(),
            $processableStatuses,
            true
        );
    }

    /**
     * @throws OutputInterfaceMustBeSet
     */
    private function parseDateString(?string $dateString): ?Carbon
    {
        if (!$dateString) {
            return null;
        }
        try {
            return Carbon::createFromFormat('Y-m-d', $dateString);
        } catch (Exception) {
            $this->signaling->console('<error>Invalid date format. Please use YYYY-MM-DD.</error>');
            return null;
        }
    }
}
