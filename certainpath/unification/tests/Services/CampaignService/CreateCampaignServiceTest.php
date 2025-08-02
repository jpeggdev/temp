<?php

namespace App\Tests\Services\CampaignService;

use App\DTO\Domain\ProspectFilterRulesDTO;
use App\DTO\Request\Campaign\CreateCampaignDTO;
use App\Entity\Batch;
use App\Entity\BatchStatus;
use App\Entity\Campaign;
use App\Entity\CampaignIterationStatus;
use App\Entity\Company;
use App\Entity\Prospect;
use App\Entity\Tag;
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
use App\Repository\CampaignRepository;
use App\Services\Campaign\CreateCampaignService;
use App\Services\CampaignEventService;
use App\Services\CampaignIteration\CreateCampaignIterationService;
use App\Services\MailPackageService;
use App\Services\ProspectFilterRule\ProspectFilterRuleRegistry;
use App\Services\ProspectFilterRule\ProspectFilterRuleService;
use App\Tests\FunctionalTestCase;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;
use Monolog\Handler\IFTTTHandler;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class CreateCampaignServiceTest extends FunctionalTestCase
{
    private CampaignEventService $campaignEventService;
    private CreateCampaignService $campaignService;
    private CampaignRepository $campaignRepository;

    public function setUp(): void
    {
        parent::setUp();

        $logger = $this->getService(LoggerInterface::class);
        $entityManager = $this->getService(EntityManagerInterface::class);
        $messageBus = $this->getService(MessageBusInterface::class);
        $this->campaignEventService = $this->createMock(CampaignEventService::class);

        $this->campaignRepository = $this->getCampaignRepository();
        $companyRepository = $this->getCompanyRepository();
        $campaignStatusRepository = $this->getCampaignStatusRepository();
        $locationRepository = $this->getLocationRepository();

        $mailPackageService = $this->getService(MailPackageService::class);
        $prospectFilterRuleService = $this->getService(ProspectFilterRuleService::class);
        $campaignIterationService = $this->getService(CreateCampaignIterationService::class);

        $this->campaignService = new CreateCampaignService(
            $logger,
            $entityManager,
            $messageBus,
            $companyRepository,
            $this->campaignRepository,
            $locationRepository,
            $campaignStatusRepository,
            $mailPackageService,
            $this->campaignEventService,
            $prospectFilterRuleService,
            $campaignIterationService,
        );
    }

    /**
     * @throws ORMException
     * @throws \JsonException
     * @throws BatchNotFoundException
     * @throws CompanyNotFoundException
     * @throws BatchStatusNotFoundException
     * @throws MailPackageNotFoundException
     * @throws CampaignStatusNotFoundException
     * @throws CampaignAlreadyCreatedException
     * @throws CampaignCreationFailedException
     * @throws CampaignAlreadyCompletedException
     * @throws CampaignAlreadyProcessingException
     * @throws CampaignIterationNotFoundException
     * @throws CampaignIterationCannotBeCreatedException
     * @throws CampaignIterationStatusNotFoundException
     * @throws CampaignIterationWeekNotFoundException
     * @throws ProspectFilterRuleNotFoundException
     */
    public function testCreateCampaignAsyncThrowsExceptionIfCampaignIsAlreadyCreated(): void
    {
        $prospectFilterRulesDTO = new ProspectFilterRulesDTO(
            ProspectFilterRuleRegistry::INCLUDE_PROSPECTS_ONLY_VALUE
        );

        $createCampaignDTO = $this->prepareCreateCampaignDTO(
            prospectFilterRules: $prospectFilterRulesDTO
        );

        $this->campaignEventService
            ->method('isCampaignCreated')
            ->with($createCampaignDTO)
            ->willReturn(true);

        $this->expectException(CampaignAlreadyCreatedException::class);

        $this->campaignService->createCampaignSync($createCampaignDTO);
    }

    /**
     * @throws \JsonException
     * @throws ORMException
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
     * @throws CampaignIterationCannotBeCreatedException
     * @throws CampaignIterationStatusNotFoundException
     * @throws CampaignIterationWeekNotFoundException
     */
    public function testCreateCampaignAsyncThrowsExceptionIfCampaignIsPending(): void
    {
        $dto = $this->prepareCreateCampaignDTO();

        $this->campaignEventService
            ->method('isCampaignPending')
            ->with($dto)
            ->willReturn(true);

        $this->expectException(CampaignAlreadyProcessingException::class);

        $this->campaignService->createCampaignSync($dto);
    }

    /**
     * @throws ORMException
     * @throws CompanyNotFoundException
     * @throws CampaignAlreadyCreatedException
     * @throws CampaignCreationFailedException
     * @throws CampaignStatusNotFoundException
     * @throws CampaignAlreadyProcessingException
     */
    public function testCreateCampaignAsyncCreatesCampaignEventsAndDispatchesMessages(): void
    {
        $company = $this->initializeCompany();
        $dto = $this->prepareCreateCampaignDTO($company);

        $this->initializeProspectFilterRules();
        $this->initializeCampaignStatuses();
        $this->initializeCampaignIterationStatuses();
        $this->initializeBatchStatuses();
        $this->initializeEventStatuses();

        // CampaignService::checkCampaignCanBeCreated
        $this->campaignEventService
            ->method('isCampaignFailed')
            ->with($dto)
            ->willReturn(false);

        $this->campaignEventService
            ->method('isCampaignPending')
            ->with($dto)
            ->willReturn(false);

        // CampaignService::createCampaign
        $this->campaignEventService
            ->expects($this->once())
            ->method('createCampaignPendingEvent')
            ->with($this->isInstanceOf(CreateCampaignDTO::class));

        $this->campaignService->createCampaignAsync($dto);
    }

    /**
     * @return void
     * @throws CampaignAlreadyCreatedException
     * @throws CampaignAlreadyProcessingException
     * @throws CampaignCreationFailedException
     * @throws CampaignStatusNotFoundException
     * @throws CompanyNotFoundException
     * @throws ORMException
     */
    public function testCreateCampaignAsyncRespectsTestTag1(): void
    {
        $company = $this->initializeCompany();
        $mailPackage = $this->initializeMailPackage();
        $this->initializeProspectsWithTags($company);

        $prospectFilterRulesDTO = $this->prepareProspectFilterRulesDTO($company->getIdentifier());
        $prospectFilterRulesDTO->tags = [
            'test_tag1',
        ];

        $createCampaignDTO = $this->prepareCreateCampaignDTO(
            company: $company,
            mailPackage: $mailPackage,
            prospectFilterRules: $prospectFilterRulesDTO
        );
        $newCampaign = $this->initializeCampaignAsync($createCampaignDTO);
        $batches = $newCampaign->getBatches();

        $this->assertNotEmpty($batches);

        /*
         * There are 19 total Prospects with test_tag2,
         */
        foreach ($batches as $batch) {
            if ($batch !== $batches->last()) {
                $this->assertCount(3, $batch->getProspects());
            } else {
                $this->assertCount(4, $batch->getProspects());
            }
        }
    }

    /**
     * @return void
     * @throws CampaignAlreadyCreatedException
     * @throws CampaignAlreadyProcessingException
     * @throws CampaignCreationFailedException
     * @throws CampaignStatusNotFoundException
     * @throws CompanyNotFoundException
     * @throws ORMException
     */
    public function testCreateCampaignAsyncRespectsTestTag2(): void
    {
        $company = $this->initializeCompany();
        $mailPackage = $this->initializeMailPackage();
        $this->initializeProspectsWithTags($company);

        $prospectFilterRulesDTO = $this->prepareProspectFilterRulesDTO($company->getIdentifier());
        $prospectFilterRulesDTO->tags = [
            'test_tag2',
        ];

        $createCampaignDTO = $this->prepareCreateCampaignDTO(
            company: $company,
            mailPackage: $mailPackage,
            prospectFilterRules: $prospectFilterRulesDTO
        );
        $newCampaign = $this->initializeCampaignAsync($createCampaignDTO);
        $batches = $newCampaign->getBatches();

        $this->assertNotEmpty($batches);

        /*
         * There are 13 total Prospects with test_tag2,
         */
        foreach ($batches as $batch) {
            if ($batch !== $batches->last()) {
                $this->assertCount(2, $batch->getProspects());
            } else {
                $this->assertCount(3, $batch->getProspects());
            }
        }
    }

    /**
     * @return void
     * @throws CampaignAlreadyCreatedException
     * @throws CampaignAlreadyProcessingException
     * @throws CampaignCreationFailedException
     * @throws CampaignStatusNotFoundException
     * @throws CompanyNotFoundException
     * @throws ORMException
     */
    public function testCreateCampaignAsyncRespectsTestTag1AndTestTag2(): void
    {
        $company = $this->initializeCompany();
        $mailPackage = $this->initializeMailPackage();
        $this->initializeProspectsWithTags($company);

        $prospectFilterRulesDTO = $this->prepareProspectFilterRulesDTO($company->getIdentifier());
        $prospectFilterRulesDTO->tags = [
            'test_tag1',
            'test_tag2',
        ];

        $createCampaignDTO = $this->prepareCreateCampaignDTO(
            company: $company,
            mailPackage: $mailPackage,
            prospectFilterRules: $prospectFilterRulesDTO
        );
        $newCampaign = $this->initializeCampaignAsync($createCampaignDTO);
        $batches = $newCampaign->getBatches();

        $this->assertNotEmpty($batches);

        /*
         * There are 25 total Prospects with either test_tag1 or test_tag2,
         */
        foreach ($batches as $batch) {
            if ($batch !== $batches->last()) {
                $this->assertCount(4, $batch->getProspects());
            } else {
                $this->assertCount(5, $batch->getProspects());
            }
        }
    }

    /**
     * @return Campaign
     * @throws ORMException
     * @throws CompanyNotFoundException
     * @throws CampaignAlreadyCreatedException
     * @throws CampaignAlreadyProcessingException
     * @throws CampaignCreationFailedException
     * @throws CampaignStatusNotFoundException
     */
    public function testCreateCampaignAsyncCreatesIterationsAndBatches(): Campaign
    {
        $company = $this->initializeCompany();
        $mailPackage = $this->initializeMailPackage();

        $prospectFilterRulesDTO = $this->prepareProspectFilterRulesDTO($company->getIdentifier());
        $createCampaignDTO = $this->prepareCreateCampaignDTO(
            company: $company,
            mailPackage: $mailPackage,
            prospectFilterRules: $prospectFilterRulesDTO
        );

        $this->initializeCampaignAsync($createCampaignDTO);

        $campaign = $this->campaignRepository->findOneByName($createCampaignDTO->name);
        $batches = $campaign->getBatches();
        $campaignIterations = $campaign->getCampaignIterations();
        $campaignIterationsWeeksCount = $this->calculateTotalCampaignIterationWeeks($campaignIterations);

        $this->assertNotEmpty($batches);
        $this->assertNotEmpty($campaignIterations);

        $this->assertCount(
            $createCampaignDTO->mailingFrequencyWeeks,
            $batches
        );
        $this->assertEquals(
            BatchStatus::STATUS_NEW,
            $batches->first()->getBatchStatus()->getName()
        );
        $this->assertCount(
            3, // Check the first iteration batch contains prospectsPerBatch prospects
            $batches->first()->getProspects()
        );
        $this->assertCount(
            5, // Check the last iteration batch contains remaining prospects
            $batches->get(5)->getProspects()
        );

        $this->assertCount(
            9, // [8 complete and 1 partial] iterations are created for a 12-month campaign
            $campaignIterations
        );
        $this->assertEquals(
            CampaignIterationStatus::STATUS_ACTIVE,
            $campaignIterations->first()->getCampaignIterationStatus()->getName()
        );
        $this->assertEquals(
            CampaignIterationStatus::STATUS_PENDING,
            $campaignIterations->get(1)->getCampaignIterationStatus()->getName()
        );

        $this->assertEquals(
            53, // [52 complete and 1] incomplete weeks in the one-year campaign
            $campaignIterationsWeeksCount
        );

        return $campaign;
    }

    /**
     * @throws ORMException
     * @throws \JsonException
     * @throws CompanyNotFoundException
     * @throws CampaignCreationFailedException
     * @throws CampaignStatusNotFoundException
     * @throws CampaignAlreadyCreatedException
     * @throws CampaignAlreadyProcessingException
     */
    public function testCreateCampaignAsyncWithPostalCodeProspectFilterRuleApplied(): void
    {
        $company = $this->initializeCompany();
        $mailPackage = $this->initializeMailPackage();
        $mailingDropWeeks = [1, 2];
        $mailingFrequencyWeeks = 2;
        $postalCodes = ['12341' => 2];
        $campaignProspectFilterRulesData = [];

        $prospectFilterRulesDTO = new ProspectFilterRulesDTO(
            intacctId: $company->getIdentifier(),
            postalCodes: $postalCodes
        );

        $createCampaignDto = $this->prepareCreateCampaignDTO(
            company: $company,
            mailPackage: $mailPackage,
            mailingFrequencyWeeks: $mailingFrequencyWeeks,
            mailingDropWeeks: $mailingDropWeeks,
            prospectFilterRules: $prospectFilterRulesDTO
        );

        $expectedProspectFilterRuleNames = [
            ProspectFilterRuleRegistry::CUSTOMER_INCLUSION_RULE_NAME,
            ProspectFilterRuleRegistry::POSTAL_CODE_LIMITS_RULE_NAME,
            ProspectFilterRuleRegistry::ADDRESS_TYPE_INCLUSION_RULE_NAME,
        ];

        $this->initializeFilterableProspectsWithPostalCodes($company);
        $this->initializeCampaignAsync($createCampaignDto, false);


        $campaign = $this->campaignRepository->findOneByName($createCampaignDto->name);
        $this->assertNotNull($campaign);
        $this->assertEquals($mailingDropWeeks, $campaign->getMailingDropWeeks());
        $this->assertEquals($mailingFrequencyWeeks, $campaign->getMailingFrequencyWeeks());

        $batches = $campaign->getBatches();
        $this->assertEquals(2, $batches->count());

        $prospectFilterRules = $campaign->getProspectFilterRules();
        $this->assertCount(3, $prospectFilterRules);

        foreach ($prospectFilterRules as $rule) {
            $campaignProspectFilterRulesData[$rule->getName()] = $rule->getValue();
        }

        foreach ($batches as $batch) {
            $this->assertEquals(1, $batch->getProspects()->count());
            $prospect = $batch->getProspects()->first();

            $this->assertEquals(
                array_keys($postalCodes)[0],
                $prospect->getPreferredAddress()->getPostalCodeShort()
            );
        }

        foreach ($campaignProspectFilterRulesData as $ruleName => $value) {
            $this->assertContains($ruleName, $expectedProspectFilterRuleNames);

            if ($ruleName === ProspectFilterRuleRegistry::POSTAL_CODE_LIMITS_RULE_NAME) {
                $postalCodeFilterRule = json_decode($value, true, 512, JSON_THROW_ON_ERROR);
                $postalCodeFilterRulePostalCode = array_keys($postalCodeFilterRule)[0];
                $postalCodeFilterRuleProspectsCount = array_values($postalCodeFilterRule)[0];

                $this->assertArrayHasKey($postalCodeFilterRulePostalCode, $postalCodes);
                $this->assertContains($postalCodeFilterRuleProspectsCount, $postalCodes);
            }
        }
    }

    /**
     * @throws ORMException
     * @throws CampaignAlreadyCreatedException
     * @throws CampaignAlreadyProcessingException
     * @throws CampaignCreationFailedException
     * @throws CampaignStatusNotFoundException
     * @throws CompanyNotFoundException
     */
    public function testCreateCampaignAsyncWithMinEstimatedIncomeProspectFilterRuleApplied(): void
    {
        $company = $this->initializeCompany();
        $mailPackage = $this->initializeMailPackage();
        $mailingDropWeeks = [1, 2];
        $mailingFrequencyWeeks = 2;

        $expectedEstimatedIncome = 9;
        $expectedProspectFilterRules = [
            ProspectFilterRuleRegistry::CUSTOMER_INCLUSION_RULE_NAME,
            ProspectFilterRuleRegistry::MIN_ESTIMATED_INCOME_RULE_NAME,
            ProspectFilterRuleRegistry::ADDRESS_TYPE_INCLUSION_RULE_NAME,
        ];

        $prospectFilterRulesDTO = new ProspectFilterRulesDTO(
            $company->getIdentifier(),
            minEstimatedIncome: $expectedEstimatedIncome
        );

        $createCampaignDto = $this->prepareCreateCampaignDTO(
            company: $company,
            mailPackage: $mailPackage,
            mailingFrequencyWeeks: $mailingFrequencyWeeks,
            mailingDropWeeks: $mailingDropWeeks,
            prospectFilterRules: $prospectFilterRulesDTO
        );

        $this->initializeFilterableProspectsWithEstimatedIncome($company);
        $this->initializeCampaignAsync($createCampaignDto, false);

        $campaign = $this->campaignRepository->findOneByName($createCampaignDto->name);
        $this->assertNotNull($campaign);
        $this->assertEquals($mailingDropWeeks, $campaign->getMailingDropWeeks());
        $this->assertEquals($mailingFrequencyWeeks, $campaign->getMailingFrequencyWeeks());

        foreach ($campaign->getProspectFilterRules() as $rule) {
            $this->assertContains($rule->getName(), $expectedProspectFilterRules);

            if ($rule->getName() === ProspectFilterRuleRegistry::MIN_ESTIMATED_INCOME_RULE_NAME) {
                $this->assertEquals($expectedEstimatedIncome, $rule->getValue());
            }
        }

        foreach ($campaign->getBatches() as $batch) {
            foreach ($batch->getProspects() as $prospect) {
                $prospectDetails = $prospect->getProspectDetails();
                $this->assertEquals($expectedEstimatedIncome, $prospectDetails->getEstimatedIncome());
            }
        }
    }

    /**
     * @throws CampaignCreationFailedException
     * @throws CompanyNotFoundException
     * @throws CampaignAlreadyProcessingException
     * @throws ORMException
     * @throws CampaignStatusNotFoundException
     * @throws CampaignAlreadyCreatedException
     */
    public function testCreateCampaignAsyncWithAddressTypeCommercialRuleApplied(): void
    {
        $company = $this->initializeCompany();
        $mailPackage = $this->initializeMailPackage();
        $mailingDropWeeks = [1];
        $mailingFrequencyWeeks = 1;

        $expectedProspectFilterRules = [
            ProspectFilterRuleRegistry::CUSTOMER_INCLUSION_RULE_NAME,
            ProspectFilterRuleRegistry::ADDRESS_TYPE_INCLUSION_RULE_NAME,
        ];

        $prospectFilterRulesDTO = new ProspectFilterRulesDTO(
            $company->getIdentifier(),
            addressTypeRule: ProspectFilterRuleRegistry::INCLUDE_COMMERCIAL_ONLY_RULE_VALUE
        );

        $createCampaignDto = $this->prepareCreateCampaignDTO(
            company: $company,
            mailPackage: $mailPackage,
            mailingFrequencyWeeks: $mailingFrequencyWeeks,
            mailingDropWeeks: $mailingDropWeeks,
            prospectFilterRules: $prospectFilterRulesDTO
        );

        $residentialAddress = $this->initializeAddress(company: $company);
        $commercialAddress = $this->initializeAddress(company: $company, isBusiness: true);

        $this->initializeProspect(
            company: $company,
            address: $residentialAddress,
            firstName: 'prospect_residential'
        );
        $this->initializeProspect(
            company: $company,
            address: $commercialAddress,
            firstName: 'prospect_commercial'
        );

        $this->initializeCampaignAsync($createCampaignDto, false);
        $campaign = $this->campaignRepository->findOneByName($createCampaignDto->name);
        $this->assertNotNull($campaign);
        $this->assertEquals($mailingDropWeeks, $campaign->getMailingDropWeeks());
        $this->assertEquals($mailingFrequencyWeeks, $campaign->getMailingFrequencyWeeks());

        $prospectFilterRules = $campaign->getProspectFilterRules();
        $this->assertCount(2, $prospectFilterRules);

        foreach ($prospectFilterRules as $rule) {
            $this->assertContains($rule->getName(), $expectedProspectFilterRules);
        }

        $batches = $campaign->getBatches();
        $this->assertEquals(1, $batches->count());

        /** @var Batch $batch */
        foreach ($campaign->getBatches() as $batch) {
            $prospects = $batch->getProspects();
            $this->assertEquals(1, $batch->getProspects()->count());

            /** @var Prospect $prospect */
            foreach ($prospects as $prospect) {
                $prospectPreferredAddress = $prospect->getPreferredAddress();
                $this->assertNotEmpty($prospectPreferredAddress);
                $this->assertTrue($prospectPreferredAddress->isBusiness());
            }
        }
    }

    /**
     * @throws CampaignCreationFailedException
     * @throws CompanyNotFoundException
     * @throws CampaignAlreadyProcessingException
     * @throws ORMException
     * @throws CampaignStatusNotFoundException
     * @throws CampaignAlreadyCreatedException
     */
    public function testCreateCampaignAsyncWithAddressTypeBothResidentialAndCommercialRuleApplied(): void
    {
        $company = $this->initializeCompany();
        $mailPackage = $this->initializeMailPackage();
        $mailingDropWeeks = [1];
        $mailingFrequencyWeeks = 1;

        $expectedProspectFilterRules = [
            ProspectFilterRuleRegistry::CUSTOMER_INCLUSION_RULE_NAME,
            ProspectFilterRuleRegistry::ADDRESS_TYPE_INCLUSION_RULE_NAME,
        ];

        $prospectFilterRulesDTO = new ProspectFilterRulesDTO(
            $company->getIdentifier(),
            addressTypeRule: ProspectFilterRuleRegistry::INCLUDE_BOTH_RESIDENTIAL_AND_COMMERCIAL_RULE_VALUE
        );

        $createCampaignDto = $this->prepareCreateCampaignDTO(
            company: $company,
            mailPackage: $mailPackage,
            mailingFrequencyWeeks: $mailingFrequencyWeeks,
            mailingDropWeeks: $mailingDropWeeks,
            prospectFilterRules: $prospectFilterRulesDTO
        );

        $residentialAddress = $this->initializeAddress(company: $company);
        $commercialAddress = $this->initializeAddress(company: $company, isBusiness: true);

        $this->initializeProspect(
            company: $company,
            address: $residentialAddress,
            firstName: 'prospect_residential'
        );
        $this->initializeProspect(
            company: $company,
            address: $commercialAddress,
            firstName: 'prospect_commercial'
        );

        $this->initializeCampaignAsync($createCampaignDto, false);
        $campaign = $this->campaignRepository->findOneByName($createCampaignDto->name);
        $this->assertNotNull($campaign);
        $this->assertEquals($mailingDropWeeks, $campaign->getMailingDropWeeks());
        $this->assertEquals($mailingFrequencyWeeks, $campaign->getMailingFrequencyWeeks());

        $prospectFilterRules = $campaign->getProspectFilterRules();
        $this->assertCount(2, $prospectFilterRules);

        foreach ($prospectFilterRules as $rule) {
            $this->assertContains($rule->getName(), $expectedProspectFilterRules);
        }

        $batches = $campaign->getBatches();
        $this->assertEquals(1, $batches->count());

        /** @var Batch $batch */
        foreach ($campaign->getBatches() as $batch) {
            $prospects = $batch->getProspects();
            $this->assertEquals(2, $batch->getProspects()->count());

            /** @var Prospect $prospect */
            foreach ($prospects as $prospect) {
                $prospectPreferredAddress = $prospect->getPreferredAddress();
                $this->assertNotEmpty($prospectPreferredAddress);

                if ($prospect->getFirstName() === 'prospect_residential') {
                    $this->assertFalse($prospectPreferredAddress->isBusiness());
                }

                if ($prospect->getFirstName() === 'prospect_commercial') {
                    $this->assertTrue($prospectPreferredAddress->isBusiness());
                }
            }
        }
    }

    private function initializeProspectsWithTags(Company $company): void
    {
        /*
         * We're going to create 37 Prospects and tag the %2 ones with test_tag1,
         * so 19 of our Prospects will have test_tag1. We're going to tag the %3 ones
         * with test_tag2, so 13 of our Prospects will have test_tag2. 25 Prospects will
         * have either test_tag1 or test_tag2.
        */

        $numTaggedProspects = 37;
        $prospects = $this->initializeProspects(
            $company,
            $numTaggedProspects
        );

        $tag1 = (new Tag())
            ->setName('test_tag1')
            ->setCompany($company);
        $this->getTagRepository()->save($tag1);
        $tag2 = (new Tag())
            ->setName('test_tag2')
            ->setCompany($company);
        $this->getTagRepository()->save($tag2);

        for ($i = 0; $i < $numTaggedProspects; $i++) {
            if ($i % 2 === 0) {
                $prospects->get($i)
                    ->addTag($tag1);
            }

            if ($i % 3 === 0) {
                $prospects->get($i)
                    ->addTag($tag2);
            }
        }
    }

    /**
     * @throws ORMException
     */
    private function calculateTotalCampaignIterationWeeks($campaignIterations): int
    {
        return array_sum(
            $campaignIterations->map(function ($iteration) {
                $this->getCampaignIterationRepository()->refreshCampaignIteration($iteration);
                return $iteration->getCampaignIterationWeeks()->count();
            })->toArray()
        );
    }

    /**
     * @throws ORMException
     */
    private function initializeFilterableProspectsWithPostalCodes(Company $company): void
    {
        for ($i = 0; $i < 20; $i++) {
            $address = $this->initializeAddress($company, postalCode: '12341-11111');

            $prospect = $this->initializeProspect($company, $address);
            $this->getProspectRepository()->saveProspect($prospect);

            $this->entityManager->refresh($company);
        }
    }

    private function initializeFilterableProspectsWithEstimatedIncome(Company $company): void
    {
        $estimatedIncomeValues = [1, 3, 9];
        $estimatedIncomeValuesCount = count($estimatedIncomeValues);

        for ($i = 0; $i < 9; $i++) {
            $address = $this->initializeAddress($company, postalCode: '12341-11111');

            $prospect = $this->initializeProspect($company, $address);
            $prospectDetailsRecord = $this->initializeProspectDetails(
                $prospect,
                $i,
                $i,
                $estimatedIncomeValues[$i % $estimatedIncomeValuesCount]
            );

            $this->getProspectRepository()->saveProspect($prospect);
            $this->getProspectDetailsRepository()->saveProspectDetails($prospectDetailsRecord);
        }
    }
}
