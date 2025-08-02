<?php

namespace App\Tests\Services\CampaignService;

use App\DTO\Domain\ProspectFilterRulesDTO;
use App\DTO\Request\Campaign\CreateCampaignDTO;
use App\Entity\CampaignIterationStatus;
use App\Exceptions\DomainException\Campaign\CampaignAlreadyCreatedException;
use App\Exceptions\DomainException\Campaign\CampaignAlreadyProcessingException;
use App\Exceptions\DomainException\Campaign\CampaignCreationFailedException;
use App\Exceptions\DomainException\CampaignIteration\CampaignIterationCannotBeProcessedException;
use App\Exceptions\NotFoundException\BatchNotFoundException;
use App\Exceptions\NotFoundException\BatchStatusNotFoundException;
use App\Exceptions\NotFoundException\CampaignIterationStatusNotFoundException;
use App\Exceptions\NotFoundException\CampaignNotFoundException;
use App\Exceptions\NotFoundException\CampaignStatusNotFoundException;
use App\Exceptions\NotFoundException\CompanyNotFoundException;
use App\Exceptions\NotFoundException\MailPackageNotFoundException;
use App\Exceptions\NotFoundException\ProspectFilterRuleNotFoundException;
use App\Services\CampaignIteration\ProcessPendingCampaignIterationService;
use App\Tests\FunctionalTestCase;
use Carbon\Carbon;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\Exception\ORMException;

class ProcessPendingCampaignIterationTest extends FunctionalTestCase
{
    private ProcessPendingCampaignIterationService $processPendingCampaignIterationService;
    private CreateCampaignDTO $dto;

    /**
     * @throws Exception
     * @throws ORMException
     * @throws CompanyNotFoundException
     * @throws CampaignCreationFailedException
     * @throws CampaignStatusNotFoundException
     * @throws CampaignAlreadyCreatedException
     * @throws CampaignAlreadyProcessingException
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->processPendingCampaignIterationService = $this->getProcessPendingCampaignIterationService();

        $mailPackage = $this->initializeMailPackage();
        $company = $this->initializeCompany();
        $this->dto = $this->prepareCreateCampaignDTO(
            company: $company,
            mailPackage: $mailPackage,
            prospectFilterRules: new ProspectFilterRulesDTO()
        );
        $this->initializeCampaignAsync($this->dto);
    }

    public function tearDown(): void
    {
        $this->entityManager->close();
        unset($this->faker);

        parent::tearDown();
    }

    /**
     * @throws ORMException
     * @throws \JsonException
     * @throws BatchNotFoundException
     * @throws BatchStatusNotFoundException
     * @throws CampaignIterationCannotBeProcessedException
     * @throws CampaignIterationStatusNotFoundException
     * @throws CampaignNotFoundException
     * @throws CompanyNotFoundException
     * @throws MailPackageNotFoundException
     * @throws ProspectFilterRuleNotFoundException
     */
    public function testProcessPendingCampaignIterationSuccess(): void
    {
        $campaignRepository = $this->getCampaignRepository();
        $campaignIterationRepository = $this->getCampaignIterationRepository();
        $dateToday = Carbon::today();

        $campaign = $campaignRepository->findOneByName($this->dto->name);
        $campaignIterations = $campaign->getCampaignIterations();
        $this->assertTrue($campaignIterations->count() > 1);
        [$campaignIteration1, $campaignIteration2] = $campaignIterations;
        $nextPendingCampaignIteration = $campaignIterationRepository->findNextPendingByCampaignId($campaign->getId());
        $expectedStatus = $dateToday > $nextPendingCampaignIteration->getEndDate()
            ? CampaignIterationStatus::STATUS_COMPLETED
            : CampaignIterationStatus::STATUS_PENDING;

        $this->assertEquals($campaignIteration2->getId(), $nextPendingCampaignIteration->getId());
        $this->assertEquals(2, $campaignIteration2->getIterationNumber());

        $this->assertEquals(
            CampaignIterationStatus::STATUS_ACTIVE,
            $campaignIteration1->getCampaignIterationStatus()->getName()
        );
        $this->assertEquals(
            CampaignIterationStatus::STATUS_PENDING,
            $campaignIteration2->getCampaignIterationStatus()->getName()
        );
        $this->assertEquals(
            CampaignIterationStatus::STATUS_PENDING,
            $nextPendingCampaignIteration->getCampaignIterationStatus()->getName()
        );

        $this->assertEmpty($nextPendingCampaignIteration->getBatches());

        $this->processPendingCampaignIterationService->processPendingCampaignIteration($nextPendingCampaignIteration);
        $campaignIterationRepository->refreshCampaignIteration($nextPendingCampaignIteration);
        $campaignIterationRepository->refreshCampaignIteration($campaignIteration1);

        $this->assertEquals(
            $expectedStatus,
            $nextPendingCampaignIteration->getCampaignIterationStatus()->getName()
        );

        $this->assertCount(
            6,
            $nextPendingCampaignIteration->getBatches()
        );
        $this->assertCount(
            6,
            $nextPendingCampaignIteration->getCampaignIterationWeeks()
        );
    }
}
