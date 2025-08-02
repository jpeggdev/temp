<?php

namespace App\Tests\Services;

use App\Entity\Campaign;
use App\Entity\CampaignEvent;
use App\Entity\CampaignStatus;
use App\Entity\Company;
use App\Entity\EventStatus;
use App\Exceptions\DomainException\Campaign\CampaignAlreadyCompletedException;
use App\Exceptions\DomainException\Campaign\CampaignAlreadyCreatedException;
use App\Exceptions\DomainException\Campaign\CampaignAlreadyProcessingException;
use App\Exceptions\DomainException\Campaign\CampaignCreationFailedException;
use App\Exceptions\DomainException\CampaignIteration\CampaignIterationCannotBeCreatedException;
use App\Exceptions\NotFoundException\BatchNotFoundException;
use App\Exceptions\NotFoundException\BatchStatusNotFoundException;
use App\Exceptions\NotFoundException\CampaignIterationNotFoundException;
use App\Exceptions\NotFoundException\CampaignIterationStatusNotFoundException;
use App\Exceptions\NotFoundException\CampaignIterationWeekNotFoundException;
use App\Exceptions\NotFoundException\CampaignStatusNotFoundException;
use App\Exceptions\NotFoundException\CompanyNotFoundException;
use App\Exceptions\NotFoundException\MailPackageNotFoundException;
use App\Exceptions\NotFoundException\ProspectFilterRuleNotFoundException;
use App\Services\CampaignEventService;
use App\Services\CampaignIteration\CreateCampaignIterationService;
use App\Services\CampaignIterationWeek\CreateCampaignIterationWeekService;
use App\Tests\FunctionalTestCase;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;
use Psr\Log\LoggerInterface;

class CampaignIterationServiceTest extends FunctionalTestCase
{
    private Company $company;

    private CreateCampaignIterationService $campaignIterationService;

    public function setUp(): void
    {
        parent::setUp();

        $this->company = $this->initializeCompany();

        $logger = $this->getService(LoggerInterface::class);
        $entityManager = $this->getService(EntityManagerInterface::class);

        $prospectRepository = $this->getProspectRepository();
        $campaignIterationRepository = $this->getCampaignIterationRepository();
        $campaignIterationStatusRepository = $this->getCampaignIterationStatusRepository();

        // from Chris: looks like $batchService is no-longer needed.
        //$batchService = $this->getService(BatchService::class);

        $campaignEventService = $this->getService(CampaignEventService::class);
        $campaignIterationWeekService = $this->getService(CreateCampaignIterationWeekService::class);

        $this->campaignIterationService = new CreateCampaignIterationService(
            $prospectRepository,
            $campaignIterationRepository,
            $campaignIterationStatusRepository,
            $logger,
            $entityManager,
            $campaignEventService,
            $campaignIterationWeekService,
        );
    }

    /**
     * @throws \JsonException
     * @throws BatchNotFoundException
     * @throws BatchStatusNotFoundException
     * @throws MailPackageNotFoundException
     * @throws CampaignAlreadyCreatedException
     * @throws CampaignAlreadyCompletedException
     * @throws CampaignIterationNotFoundException
     * @throws ProspectFilterRuleNotFoundException
     * @throws CampaignIterationWeekNotFoundException
     * @throws CampaignIterationStatusNotFoundException
     * @throws CampaignIterationCannotBeCreatedException
     */
    public function testCreateIterationsThrowsExceptionIfCampaignIsCreated(): void
    {
        $this->initializeCampaignStatuses();
        $this->initializeEventStatuses();
        $this->initializeCampaignIterationStatuses();
        $mailPackage = $this->initializeMailPackage();
        $campaignStatusActive = $this->getCampaignStatusRepository()->findOneByName(
            CampaignStatus::STATUS_ACTIVE
        );

        $createCampaignDTO = $this->prepareCreateCampaignDTO(
            company: $this->company,
            prospectFilterRules: $this->prepareProspectFilterRulesDTO($this->company->getIdentifier())
        );

        $campaign = (new Campaign())
            ->setCompany($this->company)
            ->setName('testCreateIterationsThrowsExceptionIfCampaignIsCreated')
            ->setCampaignStatus($campaignStatusActive)
            ->setMailPackage($mailPackage)
            ->setStartDate(new \DateTime())
            ->setEndDate(new \DateTime())
            ->setMailingFrequencyWeeks(1)
            ->setMailingDropWeeks([1]);

        $eventStatusCreated = $this->getEventStatusRepository()->findOneName(EventStatus::CREATED);

        $campaignEvent = (new CampaignEvent())
            ->setCampaign($campaign)
            ->setCampaignIdentifier($createCampaignDTO->getIdentifier())
            ->setEventStatus($eventStatusCreated);

        $this->entityManager->persist($campaign);
        $this->entityManager->persist($campaignEvent);
        $this->entityManager->flush();

        $this->expectException(CampaignAlreadyCreatedException::class);
        $this->campaignIterationService->createCampaignIterations($campaign, $createCampaignDTO);
    }

    /**
     * @throws ORMException
     * @throws \JsonException
     * @throws BatchNotFoundException
     * @throws CompanyNotFoundException
     * @throws BatchStatusNotFoundException
     * @throws MailPackageNotFoundException
     * @throws CampaignCreationFailedException
     * @throws CampaignAlreadyCreatedException
     * @throws CampaignStatusNotFoundException
     * @throws CampaignAlreadyCompletedException
     * @throws CampaignAlreadyProcessingException
     * @throws CampaignIterationNotFoundException
     * @throws ProspectFilterRuleNotFoundException
     * @throws CampaignIterationWeekNotFoundException
     * @throws CampaignIterationStatusNotFoundException
     * @throws CampaignIterationCannotBeCreatedException
     */
    public function testCreateIterationsThrowsExceptionIfCampaignIsCompleted(): void
    {
        $campaignRepository = $this->getCampaignRepository();
        $campaignStatusRepository = $this->getCampaignStatusRepository();

        $createCampaignDTO = $this->prepareCreateCampaignDTO(
            company: $this->company,
            prospectFilterRules: $this->prepareProspectFilterRulesDTO($this->company->getIdentifier())
        );

        $campaign = $this->initializeCampaignAsync($createCampaignDTO);
        $campaignStatusCompleted = $campaignStatusRepository->findOneByName(CampaignStatus::STATUS_COMPLETED);
        $campaign->setCampaignStatus($campaignStatusCompleted);
        $campaignRepository->saveCampaign($campaign);

        $qb = $this->entityManager->createQueryBuilder();
        $qb->delete(CampaignEvent::class, 'ce')
            ->where('ce.campaignIdentifier = :campaignIdentifier')
            ->setParameter('campaignIdentifier', $createCampaignDTO->getIdentifier())
            ->getQuery()
            ->execute();

        $this->expectException(CampaignAlreadyCompletedException::class);

        $this->campaignIterationService->createCampaignIterations($campaign, $createCampaignDTO);
    }
}
