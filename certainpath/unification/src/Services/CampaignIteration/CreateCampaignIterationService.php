<?php

namespace App\Services\CampaignIteration;

use App\DTO\Request\Campaign\CreateCampaignDTO;
use App\Entity\Campaign;
use App\Entity\CampaignIteration;
use App\Entity\CampaignIterationStatus;
use App\Exceptions\DomainException\Campaign\CampaignAlreadyCompletedException;
use App\Exceptions\DomainException\Campaign\CampaignAlreadyCreatedException;
use App\Exceptions\DomainException\CampaignIteration\CampaignIterationCannotBeCreatedException;
use App\Exceptions\NotFoundException\BatchNotFoundException;
use App\Exceptions\NotFoundException\BatchStatusNotFoundException;
use App\Exceptions\NotFoundException\CampaignIterationNotFoundException;
use App\Exceptions\NotFoundException\CampaignIterationStatusNotFoundException;
use App\Exceptions\NotFoundException\CampaignIterationWeekNotFoundException;
use App\Exceptions\NotFoundException\MailPackageNotFoundException;
use App\Exceptions\NotFoundException\ProspectFilterRuleNotFoundException;
use App\Repository\CampaignIterationRepository;
use App\Repository\CampaignIterationStatusRepository;
use App\Repository\ProspectRepository;
use App\Services\CampaignEventService;
use App\Services\CampaignIterationWeek\CreateCampaignIterationWeekService;
use App\ValueObjects\CampaignIterationObject;
use Carbon\Carbon;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Psr\Log\LoggerInterface;

readonly class CreateCampaignIterationService extends BaseCampaignIterationService
{
    public function __construct(
        ProspectRepository $prospectRepository,
        CampaignIterationRepository $campaignIterationRepository,
        CampaignIterationStatusRepository $campaignIterationStatusRepository,
        private LoggerInterface $logger,
        private EntityManagerInterface $entityManager,
        private CampaignEventService $campaignEventService,
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
     * @throws MailPackageNotFoundException
     * @throws BatchStatusNotFoundException
     * @throws CampaignAlreadyCreatedException
     * @throws CampaignAlreadyCompletedException
     * @throws CampaignIterationNotFoundException
     * @throws ProspectFilterRuleNotFoundException
     * @throws CampaignIterationWeekNotFoundException
     * @throws CampaignIterationStatusNotFoundException
     * @throws CampaignIterationCannotBeCreatedException
     */
    public function createCampaignIterations(
        Campaign $campaign,
        CreateCampaignDTO $dto
    ): void {
        $this->entityManager->beginTransaction();

        try {
            $this->checkCampaignIterationCanBeCreated($campaign, $dto);
            $this->campaignEventService->createCampaignProcessingEvent($dto, $campaign);

            $campaignIterationStatusActive = $this->campaignIterationStatusRepository->findOneByNameOrFail(
                CampaignIterationStatus::STATUS_ACTIVE
            );
            $campaignIterationStatusPending = $this->campaignIterationStatusRepository->findOneByNameOrFail(
                CampaignIterationStatus::STATUS_PENDING
            );
            $campaignStartDate = Carbon::instance($campaign->getStartDate());
            $campaignEndDate = Carbon::instance($campaign->getEndDate());
            $campaignIterationStartDate = $campaignStartDate->copy();
            $remainingWeeks = 0;
            $campaignWeeks = (int) floor($campaignStartDate->diffInWeeks($campaignEndDate));
            $mailingFrequencyWeeks = $campaign->getMailingFrequencyWeeks();
            $totalCampaignIterationsNumber = (int ) floor($campaignWeeks / $mailingFrequencyWeeks);
            $prospects = $this->getProspectsForProcessing($campaign);

            for (
                $campaignIterationNumber = 1;
                $campaignIterationNumber <= $totalCampaignIterationsNumber;
                $campaignIterationNumber++
            ) {
                $campaignIterationEndDate = $this->calculateCampaignIterationEndDate(
                    $campaignIterationStartDate,
                    $campaignEndDate,
                    $mailingFrequencyWeeks
                );

                $campaignIterationStatus = $campaignIterationNumber === 1
                    ? $campaignIterationStatusActive
                    : $campaignIterationStatusPending;

                $this->createCampaignIteration(
                    $campaign,
                    $prospects,
                    $campaignIterationNumber,
                    $campaignIterationStatus,
                    $campaignIterationStartDate,
                    $campaignIterationEndDate
                );

                if ($campaignIterationNumber === 1) {
                    $prospects = new ArrayCollection();
                }

                $remainingWeeks = (int) ceil($campaignIterationStartDate->diffInWeeks($campaignEndDate));
            }

            if ($remainingWeeks > 0) {
                $campaignIterationEndDate = $this->calculateCampaignIterationEndDate(
                    $campaignIterationStartDate,
                    $campaignEndDate,
                    $remainingWeeks
                );

                $this->createCampaignIteration(
                    $campaign,
                    $prospects,
                    $campaignIterationNumber,
                    $campaignIterationStatusPending,
                    $campaignIterationStartDate,
                    $campaignIterationEndDate
                );
            }

            $this->campaignEventService->createCampaignCreatedEvent(
                $dto,
                $campaign
            );
            $this->campaignEventService->createCampaignActiveEvent(
                $campaign
            );

            $this->entityManager->commit();
        } catch (Exception $e) {
            $this->handleError($e, $dto, $campaign);
            throw $e;
        }
    }

    /**
     * @throws Exception
     * @throws BatchNotFoundException
     * @throws BatchStatusNotFoundException
     * @throws CampaignIterationNotFoundException
     * @throws CampaignIterationWeekNotFoundException
     * @throws MailPackageNotFoundException
     */
    public function createCampaignIteration(
        Campaign $campaign,
        Collection $prospects,
        int $iterationNumber,
        CampaignIterationStatus $iterationStatus,
        Carbon $iterationStartDate,
        Carbon $iterationEndDate,
    ): void {
        $campaignIteration = $this->saveCampaignIteration(
            $campaign,
            $iterationNumber,
            $iterationStatus,
            $iterationStartDate,
            $iterationEndDate
        );

        $this->campaignIterationWeekService->createCampaignIterationWeeks(
            $campaign,
            $campaignIteration,
            $prospects,
            $iterationStartDate,
        );
    }

    /**
     * @throws Exception
     * @throws CampaignIterationNotFoundException
     */
    public function saveCampaignIteration(
        Campaign $campaign,
        int $iterationNumber,
        CampaignIterationStatus $iterationStatus,
        Carbon $iterationStartDate,
        Carbon $iterationEndDate
    ): CampaignIteration {
        $campaignIterationObject = (new CampaignIterationObject())
            ->fromArray([
                'campaign_id' => $campaign->getId(),
                'iteration_number' => $iterationNumber,
                'campaign_iteration_status_id' => $iterationStatus->getId(),
                'start_date' => $iterationStartDate->format('Y-m-d'),
                'end_date' => $iterationEndDate->format('Y-m-d'),
            ]);

        $lastInsertedId = $this->campaignIterationRepository->saveCampaignIterationDBAL($campaignIterationObject);
        $campaignIteration = $this->campaignIterationRepository->findOneById($lastInsertedId);

        if (!$campaignIteration) {
            throw new CampaignIterationNotFoundException();
        }

        return $campaignIteration;
    }

    private function calculateCampaignIterationEndDate(
        Carbon $campaignIterationStartDate,
        Carbon $campaignEndDate,
        int $mailingFrequencyWeeks
    ): Carbon {
        $campaignIterationEndDate = $campaignIterationStartDate
            ->copy()
            ->startOfWeek()
            ->addWeeks($mailingFrequencyWeeks - 1)
            ->endOfWeek();

        if ($campaignIterationEndDate->greaterThan($campaignEndDate)) {
            $campaignIterationEndDate = $campaignEndDate->copy();
        }

        return $campaignIterationEndDate;
    }

    /**
     * @throws CampaignAlreadyCreatedException
     * @throws CampaignAlreadyCompletedException
     * @throws CampaignIterationCannotBeCreatedException
     */
    public function checkCampaignIterationCanBeCreated(
        Campaign $campaign,
        CreateCampaignDTO $dto
    ): void {
        if ($this->campaignEventService->isCampaignCreated($dto)) {
            throw new CampaignAlreadyCreatedException();
        }

        if ($campaign->isCompleted()) {
            throw new CampaignAlreadyCompletedException();
        }

        if (!$campaign->getCompany())
        {
            throw new CampaignIterationCannotBeCreatedException();
        }
    }

    private function handleError(
        Exception $e,
        CreateCampaignDTO $createCampaignDTO,
        Campaign $campaign
    ): void {
        $this->logger->error($e->getMessage());
        $this->entityManager->rollback();
        $this->campaignEventService->createCampaignFailedEvent(
            $createCampaignDTO,
            $campaign,
            $e->getMessage()
        );
    }
}
