<?php

namespace App\Services\Campaign;

use App\DTO\Request\Campaign\CreateCampaignDTO;
use App\Entity\Campaign;
use App\Entity\CampaignStatus;
use App\Entity\Company;
use App\Entity\MailPackage;
use App\Exceptions\DomainException\Campaign\CampaignAlreadyCompletedException;
use App\Exceptions\DomainException\Campaign\CampaignAlreadyCreatedException;
use App\Exceptions\DomainException\Campaign\CampaignAlreadyProcessingException;
use App\Exceptions\DomainException\Campaign\CampaignCreationFailedException;
use App\Exceptions\DomainException\Campaign\CampaignEndDatePrecedesStartDateException;
use App\Exceptions\DomainException\Campaign\CampaignWeeksLessThanMailingFrequencyWeeksException;
use App\Exceptions\DomainException\CampaignIteration\CampaignIterationCannotBeCreatedException;
use App\Exceptions\InvalidArgumentException\InvalidDateFormatException;
use App\Exceptions\InvalidArgumentException\InvalidMailingDropWeekException;
use App\Exceptions\NotFoundException\BatchNotFoundException;
use App\Exceptions\NotFoundException\BatchStatusNotFoundException;
use App\Exceptions\NotFoundException\CampaignIterationNotFoundException;
use App\Exceptions\NotFoundException\CampaignIterationStatusNotFoundException;
use App\Exceptions\NotFoundException\CampaignIterationWeekNotFoundException;
use App\Exceptions\NotFoundException\CampaignStatusNotFoundException;
use App\Exceptions\NotFoundException\CompanyNotFoundException;
use App\Exceptions\NotFoundException\MailPackageNotFoundException;
use App\Exceptions\NotFoundException\ProspectFilterRuleNotFoundException;
use App\Message\CampaignIterations\CreateCampaignIterationsMessage;
use App\Repository\CampaignRepository;
use App\Repository\CampaignStatusRepository;
use App\Repository\CompanyRepository;
use App\Repository\LocationRepository;
use App\Services\CampaignEventService;
use App\Services\CampaignIteration\CreateCampaignIterationService;
use App\Services\MailPackageService;
use App\Services\ProspectFilterRule\ProspectFilterRuleService;
use Carbon\Carbon;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

readonly class CreateCampaignService
{
    public function __construct(
        private LoggerInterface $logger,
        private EntityManagerInterface $entityManager,
        private MessageBusInterface $messageBus,
        private CompanyRepository $companyRepository,
        private CampaignRepository $campaignRepository,
        private LocationRepository $locationRepository,
        private CampaignStatusRepository $campaignStatusRepository,
        private MailPackageService $mailPackageService,
        private CampaignEventService $campaignEventService,
        private ProspectFilterRuleService $prospectFilterRuleService,
        private CreateCampaignIterationService $campaignIterationService,
    ) {
    }

    /**
     * @throws ORMException
     * @throws \JsonException
     * @throws BatchNotFoundException
     * @throws CompanyNotFoundException
     * @throws BatchStatusNotFoundException
     * @throws MailPackageNotFoundException
     * @throws CampaignAlreadyCreatedException
     * @throws CampaignCreationFailedException
     * @throws CampaignStatusNotFoundException
     * @throws CampaignAlreadyCompletedException
     * @throws CampaignAlreadyProcessingException
     * @throws CampaignIterationNotFoundException
     * @throws ProspectFilterRuleNotFoundException
     * @throws CampaignIterationWeekNotFoundException
     * @throws CampaignIterationStatusNotFoundException
     * @throws CampaignIterationCannotBeCreatedException
     */
    public function createCampaignSync(CreateCampaignDTO $dto): Campaign
    {
        $this->checkCampaignCanBeCreated($dto);

        $campaign = $this->createCampaign($dto);
        $this->campaignIterationService->createCampaignIterations($campaign, $dto);

        return $this->campaignRepository->findOneById($campaign->getId());
    }

    /**
     * @throws ORMException
     * @throws CompanyNotFoundException
     * @throws CampaignAlreadyCreatedException
     * @throws CampaignStatusNotFoundException
     * @throws CampaignCreationFailedException
     * @throws CampaignAlreadyProcessingException
     */
    public function createCampaignAsync(CreateCampaignDTO $dto): Campaign
    {
        $this->checkCampaignCanBeCreated($dto);
        $campaign = $this->createCampaign($dto);

        $message = new CreateCampaignIterationsMessage($campaign->getId(), $dto);
        $this->messageBus->dispatch($message);

        return $campaign;
    }

    /**
     * @throws ORMException
     * @throws CompanyNotFoundException
     * @throws CampaignCreationFailedException
     * @throws CampaignStatusNotFoundException
     */
    private function createCampaign(CreateCampaignDTO $dto): Campaign
    {
        $this->entityManager->beginTransaction();

        try {
            $company = $this->companyRepository->findOneByIdentifierOrFail(
                $dto->companyIdentifier
            );
            $campaignStatus = $this->campaignStatusRepository->findOneByNameOrFail(
                CampaignStatus::STATUS_ACTIVE
            );

            $locations = new ArrayCollection();
            foreach ($dto->locationIds as $locationId) {
                $location = $this->locationRepository->findOneByIdOrFail($locationId);
                $locations->add($location);
            }

            $campaignStartDate = $this->prepareCampaignDate($dto->startDate);
            $campaignEndDate = $this->prepareCampaignDate($dto->endDate);
            $campaignWeeksTotal = (int) $campaignStartDate->diffInWeeks($campaignEndDate);
            $maxMailingDropWeek = max($dto->mailingDropWeeks);
            $mailPackage = $this->mailPackageService->createMailPackage($dto->mailPackageName);
            $mailingFrequencyWeeks = $dto->mailingFrequencyWeeks;

            if ($campaignEndDate->isBefore($campaignStartDate)) {
                throw new CampaignEndDatePrecedesStartDateException(
                    $campaignStartDate,
                    $campaignEndDate
                );
            }

            if ($campaignWeeksTotal < $mailingFrequencyWeeks) {
                throw new CampaignWeeksLessThanMailingFrequencyWeeksException(
                    $campaignWeeksTotal,
                    $mailingFrequencyWeeks
                );
            }

            if ($maxMailingDropWeek > $dto->mailingFrequencyWeeks) {
                throw new InvalidMailingDropWeekException($maxMailingDropWeek, $dto->mailingFrequencyWeeks);
            }

            if (!$mailPackage->getId()) {
                throw new MailPackageNotFoundException();
            }

            $prospectFilterRules = $this->prospectFilterRuleService->prepareProspectFilterRulesFromDTO(
                $dto->prospectFilterRules
            );

            $campaign = $this->saveCampaign(
                $dto,
                $company,
                $campaignStatus,
                $mailPackage,
                $campaignStartDate,
                $campaignEndDate,
                $prospectFilterRules,
                $locations,
            );

            $this->campaignEventService->createCampaignPendingEvent(
                $dto,
                $campaign
            );

            $this->entityManager->commit();
            $this->entityManager->refresh($campaign);

            return $campaign;
        } catch (
            CompanyNotFoundException |
            InvalidMailingDropWeekException |
            CampaignStatusNotFoundException |
            CampaignEndDatePrecedesStartDateException
            $e
        ) {
            $this->handleError($e);
            throw $e;
        } catch (Exception $e) {
            $this->handleError($e);
            throw new CampaignCreationFailedException(
                $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    private function saveCampaign(
        CreateCampaignDTO $dto,
        Company $company,
        CampaignStatus $campaignStatus,
        MailPackage $mailPackage,
        Carbon $campaignStartDate,
        Carbon $campaignEndDate,
        ArrayCollection $prospectFilterRules = null,
        ArrayCollection $locations = null,
    ): Campaign {
        $campaign = (new Campaign())
            ->setName($dto->name)
            ->setDescription($dto->description)
            ->setPhoneNumber($dto->phoneNumber)
            ->setMailingFrequencyWeeks($dto->mailingFrequencyWeeks)
            ->setMailingDropWeeks($dto->mailingDropWeeks)
            ->setStartDate($campaignStartDate)
            ->setEndDate($campaignEndDate)
            ->setCompany($company)
            ->setCampaignStatus($campaignStatus)
            ->setMailPackage($mailPackage)
            ->setHubPlusProductId($dto->hubPlusProductId);

        if ($prospectFilterRules) {
            foreach ($prospectFilterRules as $prospectFilterRule) {
                $campaign->addProspectFilterRule($prospectFilterRule);
            }
        }

        if ($locations) {
            foreach ($locations as $location) {
                $campaign->addLocation($location);
            }
        }

        return $this->campaignRepository->saveCampaign($campaign);
    }

    /**
     * @throws CampaignAlreadyCreatedException
     * @throws CampaignAlreadyProcessingException
     */
    private function checkCampaignCanBeCreated(CreateCampaignDTO $dto): void
    {
        if ($this->campaignEventService->isCampaignCreated($dto)) {
            throw new CampaignAlreadyCreatedException();
        }
        if ($this->campaignEventService->isCampaignPending($dto)) {
            throw new CampaignAlreadyProcessingException();
        }
    }

    private function prepareCampaignDate(?string $dateString): Carbon
    {
        try {
            $campaignDate = Carbon::createFromFormat('Y-m-d', $dateString);
        } catch (Exception $e) {
            throw new InvalidDateFormatException($dateString, $e->getMessage());
        }

        if (!$campaignDate) {
            throw new InvalidDateFormatException($dateString);
        }

        return $campaignDate->startOfDay();
    }

    private function handleError(Exception $e): void
    {
        $this->logger->error($e->getMessage());
        $this->entityManager->rollback();
    }
}
