<?php

namespace App\Tests;

use App\Commands\BulkMigrationCommand;
use App\DTO\Domain\ProspectFilterRulesDTO;
use App\DTO\Query\Prospect\ProspectExportMetadataDTO;
use App\DTO\Request\Campaign\CreateCampaignDTO;
use App\Entity\Address;
use App\Entity\BatchStatus;
use App\Entity\Campaign;
use App\Entity\CampaignIterationStatus;
use App\Entity\CampaignStatus;
use App\Entity\Company;
use App\Entity\Customer;
use App\Entity\EventStatus;
use App\Entity\Invoice;
use App\Entity\MailPackage;
use App\Entity\Prospect;
use App\Entity\ProspectDetails;
use App\Entity\ProspectFilterRule;
use App\Entity\Report;
use App\Entity\Tag;
use App\Entity\Trade;
use App\Exceptions\DomainException\Campaign\CampaignAlreadyCreatedException;
use App\Exceptions\DomainException\Campaign\CampaignAlreadyProcessingException;
use App\Exceptions\DomainException\Campaign\CampaignCreationFailedException;
use App\Exceptions\NotFoundException\CampaignStatusNotFoundException;
use App\Exceptions\NotFoundException\CompanyNotFoundException;
use App\Generator\BatchProspectsCsvGenerator;
use App\Generator\CompanyProspectsCsvGenerator;
use App\MessageHandler\MigrationHandler;
use App\Repository\AddressRepository;
use App\Repository\BatchRepository;
use App\Repository\BatchStatusRepository;
use App\Repository\BulkBatchStatusEventRepository;
use App\Repository\CampaignEventRepository;
use App\Repository\CampaignIterationRepository;
use App\Repository\CampaignIterationStatusRepository;
use App\Repository\CampaignRepository;
use App\Repository\CampaignStatusRepository;
use App\Repository\CompanyJobEventRepository;
use App\Repository\CompanyRepository;
use App\Repository\CustomerRepository;
use App\Repository\EventStatusRepository;
use App\Repository\InvoiceRepository;
use App\Repository\LocationRepository;
use App\Repository\MailPackageRepository;
use App\Repository\ProspectDetailsRepository;
use App\Repository\ProspectFilterRuleRepository;
use App\Repository\ProspectRepository;
use App\Repository\ReportRepository;
use App\Repository\TagRepository;
use App\Repository\TradeRepository;
use App\Repository\Unmanaged\GenericIngestRepository;
use App\Services\ApplicationSignalingService;
use App\Services\BatchService;
use App\Services\BulkMigrationService;
use App\Services\BulkUpdateBatchesStatusService;
use App\Services\Campaign\CreateCampaignService;
use App\Services\Campaign\PatchCampaignService;
use App\Services\Campaign\PauseCampaignService;
use App\Services\Campaign\ResumeCampaignService;
use App\Services\CampaignEventService;
use App\Services\CampaignIteration\CreateCampaignIterationService;
use App\Services\CampaignIteration\ProcessPendingCampaignIterationService;
use App\Services\CompanyJobService;
use App\Services\CompanyStatusService;
use App\Services\CustomerMetricsService;
use App\Services\DataProvisioner;
use App\Services\DataStreamDigestingService;
use App\Services\ExportService;
use App\Services\FileConverter;
use App\Services\LifeFileService;
use App\Services\MigrationService;
use App\Services\ProspectAggregatedService;
use App\Services\ProspectFilterRule\ProspectFilterRuleRegistry;
use App\Services\StochasticDashboard\PercentageOfNewCustomersChangeByZipCodeDataService;
use App\Services\StochasticRosterLoaderService;
use App\Services\StopCampaignService;
use App\Services\TenantStreamAuditService;
use App\Services\TradeService;
use Carbon\Carbon;
use DateMalformedStringException;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\Tools\SchemaTool;
use Random\RandomException;

class FunctionalTestCase extends AppTestCase
{
    protected EntityManagerInterface $entityManager;

    /**
     * @throws Exception
     */
    public function setUp(): void
    {
        parent::setUp();
        // Get the entity manager for the test environment
        /** @var EntityManagerInterface $manager */
        $manager = static::getContainer()->get(EntityManagerInterface::class);
        $this->entityManager = $manager;

        $databaseName = $this->entityManager->getConnection()->getDatabase();
        self::assertSame(
            'unification_test',
            $databaseName
        );

        // Drop and create the schema
        $schemaTool = new SchemaTool($this->entityManager);
        $metadata = $this->entityManager->getMetadataFactory()->getAllMetadata();

        // Drop the schema if it exists
        $schemaTool->dropSchema($metadata);

        // Create the schema
        $schemaTool->createSchema($metadata);

        $this->getDataProvisioner()->populateWorkingData();
        $this->createAndPopulateLocalIngestTables();
        $this->initializeTrades();
    }
    public function tearDown(): void
    {
        parent::tearDown();

        // Close all database connections
        if ($this->entityManager) {
            $this->entityManager->getConnection()->close();
            $this->entityManager->close();
        }

        // Clear any other service connections
        $container = static::getContainer();
        if ($container->has(GenericIngestRepository::class)) {
            $genericRepo = $container->get(GenericIngestRepository::class);
            // Close generic ingest connection if it has one
            if (method_exists($genericRepo, 'closeConnection')) {
                $genericRepo->closeConnection();
            }
        }
    }
    protected function getRepository(string $entityClass): object
    {
        return $this->entityManager->getRepository($entityClass);
    }
    protected function getAddressRepository(): AddressRepository
    {
        return $this->getRepository(
            Address::class
        );
    }
    protected function getCompanyRepository(): CompanyRepository
    {
        return $this->getRepository(
            Company::class
        );
    }
    protected function getCustomerRepository(): CustomerRepository
    {
        return $this->getRepository(
            Customer::class
        );
    }
    protected function getGenericIngestRepository(): GenericIngestRepository
    {
        return $this->getService(
            GenericIngestRepository::class
        );
    }
    protected function getInvoiceRepository(): InvoiceRepository
    {
        return $this->getRepository(
            Invoice::class
        );
    }
    protected function getProspectRepository(): ProspectRepository
    {
        return $this->getRepository(
            Prospect::class
        );
    }
    protected function getReportRepository(): ReportRepository
    {
        return $this->getRepository(
            Report::class
        );
    }
    protected function getTagRepository(): TagRepository
    {
        return $this->getRepository(
            Tag::class
        );
    }
    protected function getTradeRepository(): TradeRepository
    {
        return $this->getRepository(
            Trade::class
        );
    }
    protected function getDatabaseConsumer(): DataStreamDigestingService
    {
        return $this->getService(
            DataStreamDigestingService::class
        );
    }
    protected function getDataProvisioner(): DataProvisioner
    {
        return $this->getService(
            DataProvisioner::class
        );
    }
    protected function getFileConverter(): FileConverter
    {
        return $this->getService(
            FileConverter::class
        );
    }
    protected function getMailPackageRepository(): MailPackageRepository
    {
        return $this->getService(MailPackageRepository::class);
    }
    protected function getCampaignRepository(): CampaignRepository
    {
        return $this->getService(CampaignRepository::class);
    }
    protected function getCampaignIterationRepository(): CampaignIterationRepository
    {
        return $this->getService(CampaignIterationRepository::class);
    }
    protected function getCampaignIterationStatusRepository(): CampaignIterationStatusRepository
    {
        return $this->getService(CampaignIterationStatusRepository::class);
    }
    protected function getBatchRepository(): BatchRepository
    {
        return $this->getService(BatchRepository::class);
    }
    protected function getBatchStatusRepository(): BatchStatusRepository
    {
        return $this->getService(BatchStatusRepository::class);
    }
    protected function getBatchStatusBulkEventRepository(): BulkBatchStatusEventRepository
    {
        return $this->getService(BulkBatchStatusEventRepository::class);
    }
    protected function getCampaignStatusRepository(): CampaignStatusRepository
    {
        return $this->getService(CampaignStatusRepository::class);
    }
    protected function getLocationRepository(): LocationRepository
    {
        return $this->getService(LocationRepository::class);
    }
    protected function getEventStatusRepository(): EventStatusRepository
    {
        return $this->getService(EventStatusRepository::class);
    }
    protected function getCampaignEventRepository(): CampaignEventRepository
    {
        return $this->getService(CampaignEventRepository::class);
    }
    protected function getCompanyJobEventRepository(): CompanyJobEventRepository
    {
        return $this->getService(CompanyJobEventRepository::class);
    }
    protected function getProspectFilterRuleRepository(): ProspectFilterRuleRepository
    {
        return $this->getService(ProspectFilterRuleRepository::class);
    }

    protected function getProspectDetailsRepository(): ProspectDetailsRepository
    {
        return $this->getService(ProspectDetailsRepository::class);
    }

    protected function getStochasticRosterLoaderService(): StochasticRosterLoaderService
    {
        return $this->getService(
            StochasticRosterLoaderService::class
        );
    }

    protected function getBulkUpdateBatchesStatusService(): BulkUpdateBatchesStatusService
    {
        return $this->getService(BulkUpdateBatchesStatusService::class);
    }

    protected function getBatchService(): BatchService
    {
        return $this->getService(BatchService::class);
    }

    protected function getStopCampaignService(): StopCampaignService
    {
        return $this->getService(StopCampaignService::class);
    }

    protected function getPauseCampaignService(): PauseCampaignService
    {
        return $this->getService(PauseCampaignService::class);
    }

    protected function getResumeCampaignService(): ResumeCampaignService
    {
        return $this->getService(ResumeCampaignService::class);
    }

    protected function getPatchCampaignService(): PatchCampaignService
    {
        return $this->getService(PatchCampaignService::class);
    }

    protected function getProspectAggregatedDataService(): ProspectAggregatedService
    {
        return $this->getService(ProspectAggregatedService::class);
    }

    protected function getCampaignIterationService(): CreateCampaignIterationService
    {
        return $this->getService(CreateCampaignIterationService::class);
    }

    protected function getProcessPendingCampaignIterationService(): ProcessPendingCampaignIterationService
    {
        return $this->getService(ProcessPendingCampaignIterationService::class);
    }

    protected function getApplicationSignalingService(): ApplicationSignalingService
    {
        return $this->getService(ApplicationSignalingService::class);
    }

    protected function getCompanyProspectsCsvGenerator(): CompanyProspectsCsvGenerator
    {
        return $this->getService(
            CompanyProspectsCsvGenerator::class
        );
    }
    protected function getBatchProspectsCsvGenerator(): BatchProspectsCsvGenerator
    {
        return $this->getService(
            BatchProspectsCsvGenerator::class
        );
    }

    protected function getCampaignEventService(): CampaignEventService
    {
        return $this->getService(
            CampaignEventService::class
        );
    }
    protected function getBulkMigrationService(): BulkMigrationService
    {
        return $this->getService(
            BulkMigrationService::class
        );
    }
    protected function getMigrationService(): MigrationService
    {
        return $this->getService(
            MigrationService::class
        );
    }
    protected function getExportService(): ExportService
    {
        return $this->getService(
            ExportService::class
        );
    }
    protected function getMigrationHandler(): MigrationHandler
    {
        return $this->getService(
            MigrationHandler::class
        );
    }
    protected function getCustomerMetricsService(): CustomerMetricsService
    {
        return $this->getService(
            CustomerMetricsService::class
        );
    }

    protected function getPercentageOfNewCustomersByZipCodeTableService(): PercentageOfNewCustomersChangeByZipCodeDataService
    {
        return $this->getService(
            PercentageOfNewCustomersChangeByZipCodeDataService::class
        );
    }

    protected function getLifeFileService(): LifeFileService
    {
        return $this->getService(
            LifeFileService::class
        );
    }
    protected function getCompanyJobService(): CompanyJobService
    {
        return $this->getService(
            CompanyJobService::class
        );
    }
    protected function getTenantStreamAuditService(): TenantStreamAuditService
    {
        return $this->getService(
            TenantStreamAuditService::class
        );
    }
    protected function getTradeService(): TradeService
    {
        return $this->getService(
            TradeService::class
        );
    }
    protected function getCompanyStatusService(): CompanyStatusService
    {
        return $this->getService(
            CompanyStatusService::class
        );
    }
    protected function getBulkMigrationCommand(): BulkMigrationCommand
    {
        return $this->getService(
            BulkMigrationCommand::class
        );
    }

    protected function getCampaignService(): CreateCampaignService
    {
        return $this->getService(
            CreateCampaignService::class
        );
    }

    protected function initializeCompany(string $identifier = 'test-company'): Company
    {
        return $this->getCompanyRepository()->findActiveByIdentifierOrCreate($identifier);
    }

    protected function initializeMailPackage(
        string $name = 'Yellow Postcard',
        int $series = 123,
    ): MailPackage {
        $mailPackage = (new MailPackage())
            ->setName($name)
            ->setSeries($series);

        return $this->getMailPackageRepository()->saveMailPackage($mailPackage);
    }

    protected function initializeTrades(): ArrayCollection
    {
        $tradeRepo = $this->getTradeRepository();

        $tradesToInitialize = [
            Trade::electrical(),
            Trade::plumbing(),
            Trade::hvac(),
            Trade::roofing(),
        ];
        $initializedTrades = new ArrayCollection();

        foreach ($tradesToInitialize as $trade) {
            $initializedTrades->add(
                $tradeRepo->saveTrade($trade)
            );
        }

        self::assertTrue(
            $initializedTrades->first()->equals(Trade::electrical())
        );

        return $initializedTrades;
    }

    protected function initializeEventStatuses(): void
    {
        $eventStatusRepository = $this->getEventStatusRepository();

        $pending = EventStatus::pending();
        $processing = EventStatus::processing();
        $created = EventStatus::created();
        $failed = EventStatus::failed();
        $completed = EventStatus::completed();
        $paused = EventStatus::paused();
        $active = EventStatus::active();
        $resuming = EventStatus::resuming();

        $eventStatusRepository->saveCampaignEventStatus($pending);
        $eventStatusRepository->saveCampaignEventStatus($processing);
        $eventStatusRepository->saveCampaignEventStatus($created);
        $eventStatusRepository->saveCampaignEventStatus($failed);
        $eventStatusRepository->saveCampaignEventStatus($completed);
        $eventStatusRepository->saveCampaignEventStatus($paused);
        $eventStatusRepository->saveCampaignEventStatus($active);
        $eventStatusRepository->saveCampaignEventStatus($resuming);

        self::assertTrue(
            $pending->isPending()
        );
        self::assertTrue(
            $processing->isProcessing()
        );
        self::assertFalse(
            $created->isPending()
        );
        self::assertFalse(
            $failed->isPending()
        );
        self::assertTrue(
            $completed->isCompleted()
        );
        self::assertTrue(
            $paused->isPaused()
        );
    }

    protected function initializeCampaignStatuses(): void
    {
        $campaignStatusRepository = $this->getCampaignStatusRepository();

        $statusActive = (new CampaignStatus())
            ->setName(CampaignStatus::STATUS_ACTIVE);
        $statusPaused = (new CampaignStatus())
            ->setName(CampaignStatus::STATUS_PAUSED);
        $statusCompleted = (new CampaignStatus())
            ->setName(CampaignStatus::STATUS_COMPLETED);
        $statusArchived = (new CampaignStatus())
            ->setName(CampaignStatus::STATUS_ARCHIVED);

        $campaignStatusRepository->saveCampaignStatus($statusActive);
        $campaignStatusRepository->saveCampaignStatus($statusPaused);
        $campaignStatusRepository->saveCampaignStatus($statusCompleted);
        $campaignStatusRepository->saveCampaignStatus($statusArchived);
    }

    protected function initializeCampaignIterationStatuses(): void
    {
        $statusActive = (new CampaignIterationStatus())
            ->setName(CampaignIterationStatus::STATUS_ACTIVE);
        $statusCompleted = (new CampaignIterationStatus())
            ->setName(CampaignIterationStatus::STATUS_COMPLETED);
        $statusArchived = (new CampaignIterationStatus())
            ->setName(CampaignIterationStatus::STATUS_ARCHIVED);
        $statusPaused = (new CampaignIterationStatus())
            ->setName(CampaignIterationStatus::STATUS_PAUSED);
        $statusPending = (new CampaignIterationStatus())
            ->setName(CampaignIterationStatus::STATUS_PENDING);

        $this->getCampaignIterationStatusRepository()->saveCampaignIterationStatus($statusActive);
        $this->getCampaignIterationStatusRepository()->saveCampaignIterationStatus($statusCompleted);
        $this->getCampaignIterationStatusRepository()->saveCampaignIterationStatus($statusArchived);
        $this->getCampaignIterationStatusRepository()->saveCampaignIterationStatus($statusPaused);
        $this->getCampaignIterationStatusRepository()->saveCampaignIterationStatus($statusPending);
    }

    protected function initializeBatchStatuses(): void
    {
        $statusNew = (new BatchStatus())
            ->setName(BatchStatus::STATUS_NEW);
        $statusPaused = (new BatchStatus())
            ->setName(BatchStatus::STATUS_PAUSED);
        $statusArchived = (new BatchStatus())
            ->setName(BatchStatus::STATUS_ARCHIVED);
        $statusSent = (new BatchStatus())
            ->setName(BatchStatus::STATUS_SENT);
        $statusProcessed = (new BatchStatus())
            ->setName(BatchStatus::STATUS_PROCESSED);
        $statusInvoiced = (new BatchStatus())
            ->setName(BatchStatus::STATUS_INVOICED);
        $statusComplete = (new BatchStatus())
            ->setName(BatchStatus::STATUS_COMPLETE);


        $this->getBatchStatusRepository()->saveBatchStatus($statusNew);
        $this->getBatchStatusRepository()->saveBatchStatus($statusPaused);
        $this->getBatchStatusRepository()->saveBatchStatus($statusArchived);
        $this->getBatchStatusRepository()->saveBatchStatus($statusSent);
        $this->getBatchStatusRepository()->saveBatchStatus($statusProcessed);
        $this->getBatchStatusRepository()->saveBatchStatus($statusInvoiced);
        $this->getBatchStatusRepository()->saveBatchStatus($statusComplete);
    }

    protected function initializeProspect(
        Company $company,
        Address $address = null,
        string $firstName = '',
        string $lastName = '',
        bool $isPreferred = true,
        bool $doNotContact = false,
        bool $doNotMail = false,
    ): Prospect {
        if (!$firstName) {
            $firstName = $this->faker->firstName();
        }
        if (!$lastName) {
            $lastName = $this->faker->lastName();
        }

        $postalCode = $address
            ? $address->getPostalCode()
            : $this->faker->postcode();

        $address1 = $address
            ? $address->getAddress1()
            : $this->faker->address();

        $city = $address
            ? $address->getCity()
            : $this->faker->city();

        $state = $address
            ? $address->getStateCode()
            : 'TX';

        $prospect = (new Prospect())
            ->setFirstName($firstName)
            ->setLastName($lastName)
            ->setFullName($firstName . ' ' . $lastName)
            ->setAddress1($address1)
            ->setCity($city)
            ->setState($state)
            ->setPostalCode($postalCode)
            ->setPreferred($isPreferred)
            ->setDoNotContact($doNotContact)
            ->setDoNotMail($doNotMail)
            ->setCompany($company);

        if ($address) {
            $prospect->addAddress($address);
            $prospect->setPreferredAddress($address);
        }

        return $this->getProspectRepository()->saveProspect($prospect);
    }

    protected function initializeProspects(
        Company $company,
        int $numberOfProspects = 20,
        bool $initProspectDetails = true,
        bool $initProspectAddress = true,
    ): ArrayCollection {
        $prospects = new ArrayCollection();

        for ($i = 0; $i < $numberOfProspects; $i++) {
            $address = $initProspectAddress ? $this->initializeAddress($company) : null;
            $prospect = $this->initializeProspect($company, $address);

            if ($initProspectDetails) {
                $this->initializeProspectDetails($prospect);
            }

            $prospects->add($prospect);
        }

        return $prospects;
    }

    protected function initializeProspectDetails(
        Prospect $prospect,
        int $age = 21,
        int $yearBuilt = 1925,
        int $estimatedIncome = 1,
        mixed $infoBase = 1
    ): ProspectDetails {
        $prospectDetailsRepository = $this->getProspectDetailsRepository();

        $prospectDetails = (new ProspectDetails())
            ->setProspect($prospect)
            ->setAge($age)
            ->setYearBuilt($yearBuilt)
            ->setEstimatedIncome($estimatedIncome)
            ->setInfoBase($infoBase);

        return $prospectDetailsRepository->saveProspectDetails($prospectDetails);
    }

    public function initializeAddress(
        Company $company,
        string $address1 = '',
        string $city = '',
        string $postalCode = '',
        string $stateCode = 'TX',
        bool $isActive = true,
        bool $isDeleted = false,
        bool $isDoNotMail = false,
        bool $isGlobalDoNoMail = false,
        bool $isBusiness = false,
        bool $isVacant = false
    ): Address {
        if (!$address1) {
            $address1 = $this->faker->address();
        }
        if (!$city) {
            $city = $this->faker->city();
        }
        if (!$postalCode) {
            $postalCode = $this->faker->postcode();
        }
        if (!$stateCode) {
            $stateCode = 'TX';
        }

        $address = (new Address())
            ->setCompany($company)
            ->setAddress1($address1)
            ->setCity($city)
            ->setStateCode($stateCode)
            ->setPostalCode($postalCode)
            ->setActive($isActive)
            ->setDeleted($isDeleted)
            ->setDoNotMail($isDoNotMail)
            ->setGlobalDoNotMail($isGlobalDoNoMail)
            ->setVerifiedAt(new DateTimeImmutable())
            ->setBusiness($isBusiness)
            ->setVacant($isVacant);

        return $this->getAddressRepository()->saveAddress($address);
    }

    protected function initializeCustomer(
        Prospect $prospect,
        bool $isActive = true,
        bool $isDeleted = false,
        bool $isNewCustomer = true,
        bool $isRepeatableCustomer = false,
        bool $hasSubscription = false,
        bool $hasInstallation = false,
        string $lifetimeValue = '0.00',
    ): Customer {
        $customer = (new Customer())
            ->setCompany($prospect->getCompany())
            ->setName($prospect->getFullName())
            ->setActive($isActive)
            ->setDeleted($isDeleted)
            ->setNewCustomer($isNewCustomer)
            ->setRepeatCustomer($isRepeatableCustomer)
            ->setLifetimeValue($lifetimeValue)
            ->setHasSubscription($hasSubscription)
            ->setHasInstallation($hasInstallation)
            ->setProspect($prospect);

        return $this->getCustomerRepository()->saveCustomer($customer);
    }

    protected function initializeCustomers(ArrayCollection $prospects): ArrayCollection
    {
        $customers = new ArrayCollection();

        /** @var Prospect $prospect */
        foreach ($prospects as $prospect) {
            $customer = $this->initializeCustomer($prospect);
            $customers->add($customer);

            $prospect->setCustomer($customer);
            $this->getProspectRepository()->saveProspect($prospect);
        }

        return $customers;
    }

    protected function prepareCreateCampaignDTO(
        Company $company = null,
        MailPackage $mailPackage = null,
        int $mailingFrequencyWeeks = 6,
        string $startDate = '2024-11-11',
        string $endDate = '2025-11-11',
        array $mailingDropWeeks = [1, 2, 3, 4, 5, 6],
        int $hubPlusProductId = 1,
        ProspectFilterRulesDTO $prospectFilterRules = new ProspectFilterRulesDTO()
    ): CreateCampaignDTO {
        $name = 'Test Campaign';
        $description = 'Test Campaign Description';
        $phoneNumber = '111-222-3333';
        $mailPackageId = $mailPackage ? $mailPackage->getId() : $this->initializeMailPackage()->getId();
        $companyIdentifier = $company ? $company->getIdentifier() : $this->initializeCompany()->getIdentifier();

        return new CreateCampaignDTO(
            $name,
            $hubPlusProductId,
            $startDate,
            $endDate,
            $mailingFrequencyWeeks,
            $companyIdentifier,
            $mailPackageId,
            $description,
            $phoneNumber,
            $mailingDropWeeks,
            [],
            $prospectFilterRules
        );
    }

    protected function prepareProspectFilterRulesDTO(
        string $intecctId,
        string $customerInclusionRule = ProspectFilterRuleRegistry::INCLUDE_PROSPECTS_ONLY_VALUE,
    ): ProspectFilterRulesDTO {
        return new ProspectFilterRulesDTO(
            intacctId: $intecctId,
            customerInclusionRule: $customerInclusionRule
        );
    }

    protected function prepareExportMetadataDTO(
        string $jobNumber = '',
        string $ringTo = '',
        string $versionCode = '',
        string $csrFullName = ''
    ): ProspectExportMetadataDTO {
        return new ProspectExportMetadataDTO(
            jobNumber: $jobNumber,
            ringTo: $ringTo,
            versionCode: $versionCode,
            csr: $csrFullName
        );
    }

    /**
     * @throws ORMException
     * @throws CompanyNotFoundException
     * @throws CampaignAlreadyCreatedException
     * @throws CampaignAlreadyProcessingException
     * @throws CampaignCreationFailedException
     * @throws CampaignStatusNotFoundException
     */
    protected function initializeCampaignAsync(
        CreateCampaignDTO $dto,
        bool $initProspects = true,
        bool $initDependencies = true
    ): Campaign {
        $campaignRepository = $this->getCampaignRepository();
        $companyRepository = $this->getCompanyRepository();
        $campaignService = $this->getCampaignService();

        $company = $companyRepository->findOneByIdentifier($dto->companyIdentifier);
        $this->assertNotNull($company);

        if ($initDependencies) {
            $this->initializeBatchStatuses();
            $this->initializeCampaignStatuses();
            $this->initializeEventStatuses();
            $this->initializeCampaignIterationStatuses();
            $this->initializeProspectFilterRules();
        }

        if ($initProspects) {
            $this->initializeProspects($company);
        }

        $campaignService->createCampaignAsync($dto);
        $campaign = $campaignRepository->findOneBy(['name' => $dto->name]);

        $this->assertNotNull($campaign);

        return $campaign;
    }

    /**
     * @throws RandomException
     */
    protected function initializeInvoice(
        Customer $customer,
        Trade $trade,
        float $total = null,
        ?DateTimeImmutable $invoiceDate = null
    ): Invoice {
        $invoiceRepository = $this->getInvoiceRepository();
        $customerRepository = $this->getCustomerRepository();

        if ($total === null) {
            $total = $this->faker->randomFloat(2, 100, 1000000);
        }

        if ($invoiceDate === null) {
            $invoiceDate = $this->generateRandomInvoiceDate();
        }

        $invoice = (new Invoice())
            ->setCompany($customer->getCompany())
            ->setCustomer($customer)
            ->setTrade($trade)
            ->setTotal($total)
            ->setInvoicedAt($invoiceDate);

        $customer
            ->setCountInvoices(($customer->getCountInvoices() ?? 0) + 1)
            ->setInvoiceTotal((float) ($customer->getInvoiceTotal() ?? 0.0) + $total)
            ->setFirstInvoicedAt($customer->getFirstInvoicedAt() ?: $invoiceDate)
            ->setLastInvoicedAt($invoiceDate);

        $invoiceRepository->saveInvoice($invoice);
        $customerRepository->saveCustomer($customer);

        return $invoice;
    }

    /**
     * @throws RandomException
     * @throws DateMalformedStringException
     */
    protected function initializeInvoices(
        ArrayCollection $customers,
        Trade $trade
    ): ArrayCollection {
        $invoices = new ArrayCollection();
        $invoiceRepository = $this->getInvoiceRepository();
        $customerRepository = $this->getCustomerRepository();

        foreach ($customers as $customer) {
            $total = $this->faker->randomFloat(2, 100, 1000000);
            $invoiceDate = $this->generateRandomInvoiceDate();

            $invoice = (new Invoice())
                ->setCompany($customer->getCompany())
                ->setCustomer($customer)
                ->setTrade($trade)
                ->setTotal($total)
                ->setInvoicedAt($invoiceDate);

            $customer
                ->setCountInvoices(1)
                ->setInvoiceTotal($total)
                ->setFirstInvoicedAt($invoiceDate)
                ->setLastInvoicedAt($invoiceDate);

            $invoices->add($invoice);

            $invoiceRepository->saveInvoice($invoice);
            $customerRepository->saveCustomer($customer);
        }

        return $invoices;
    }

    /**
     * @throws RandomException
     */
    protected function generateRandomInvoiceDate(): DateTimeImmutable
    {
        $today = new DateTimeImmutable();
        $threeYearsAgo = $today->modify('-3 years');

        $randomYear = random_int($threeYearsAgo->format('Y'), $today->format('Y'));
        $randomMonth = random_int(1, 12);
        $lastDayOfMonth = Carbon::createFromDate($randomYear, $randomMonth, 1)->endOfMonth()->day;

        $randomDay = random_int(1, $lastDayOfMonth);

        $randomDate = DateTimeImmutable::createFromFormat(
            'Y-m-d',
            sprintf('%d-%02d-%02d', $randomYear, $randomMonth, $randomDay)
        );

        if ($randomDate > $today) {
            $randomDate = $today;
        }

        return $randomDate;
    }

    protected function initializeProspectFilterRules(): void
    {
        $prospectFilterRuleRepository = $this->getProspectFilterRuleRepository();

        foreach (ProspectFilterRuleRegistry::getStaticRuleDefinitions() as $ruleDefinition) {
            $rule = new ProspectFilterRule();
            $rule->setName($ruleDefinition['name']);
            $rule->setDisplayedName($ruleDefinition['displayedName']);
            $rule->setValue($ruleDefinition['value']);
            $rule->setDescription($ruleDefinition['description']);

            $prospectFilterRuleRepository->saveProspectFilterRule($rule);
        }
    }

    /**
     * @throws Exception
     */
    private function createAndPopulateLocalIngestTables(): void
    {
        if ($this->getGenericIngestRepository()->isLocalDatabase()) {
            $this->getGenericIngestRepository()->getDatabase()->executeStatement(
                'DROP TABLE IF EXISTS prospects_stream'
            );

            $this->getGenericIngestRepository()->getDatabase()->executeStatement(
                file_get_contents(__DIR__ . '/SQL/prospects_stream.sql')
            );

            $this->getGenericIngestRepository()->getDatabase()->executeStatement(
                file_get_contents(__DIR__ . '/SQL/prospects_stream_data.sql')
            );

            $this->getGenericIngestRepository()->getDatabase()->executeStatement(
                'DROP TABLE IF EXISTS members_stream'
            );

            $this->getGenericIngestRepository()->getDatabase()->executeStatement(
                file_get_contents(__DIR__ . '/SQL/members_stream.sql')
            );

            $this->getGenericIngestRepository()->getDatabase()->executeStatement(
                'DROP TABLE IF EXISTS invoices_stream'
            );

            $this->getGenericIngestRepository()->getDatabase()->executeStatement(
                file_get_contents(__DIR__ . '/SQL/invoices_stream.sql')
            );

            $this->getGenericIngestRepository()->getDatabase()->executeStatement(
                file_get_contents(__DIR__ . '/SQL/invoices_stream_data.sql')
            );
        }
    }
}
