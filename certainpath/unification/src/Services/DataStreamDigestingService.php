<?php

namespace App\Services;

use App\Entity\Company;
use App\Entity\Customer;
use App\Entity\Invoice;
use App\Entity\Prospect;
use App\Entity\ProspectDetails;
use App\Entity\Subscription;
use App\Entity\Tag;
use App\Entity\Trade;
use App\Parsers\AbstractParser;
use App\Parsers\FieldEdge\ProspectParser as FieldEdgeProspectParser;
use App\Parsers\FieldEdge\InvoiceParser as FieldEdgeInvoiceParser;
use App\Parsers\ServiceTitan\InvoicesStreamProspectParser as ServiceTitanInvoicesStreamProspectParser;
use App\Parsers\ServiceTitan\MembersStreamProspectParser as ServiceTitanMembersStreamProspectParser;
use App\Parsers\ServiceTitan\InvoiceStreamInvoiceParser as ServiceTitanInvoicesStreamInvoiceParser;
use App\Parsers\GenericIngest\InvoicesStreamProspectParser as GenericIngestInvoicesStreamProspectParser;
use App\Parsers\GenericIngest\MembersStreamProspectParser as GenericIngestMembersStreamProspectParser;
use App\Parsers\GenericIngest\ProspectsStreamProspectParser as GenericIngestProspectsStreamProspectParser;
use App\Parsers\GenericIngest\InvoiceStreamInvoiceParser as GenericIngestInvoicesStreamInvoiceParser;
use App\Parsers\Successware\ProspectParser as SuccesswareProspectParser;
use App\Repository\CompanyRepository;
use App\Repository\CustomerRepository;
use App\Repository\InvoiceRepository;
use App\Repository\ProspectDetailsRepository;
use App\Repository\ProspectRepository;
use App\Repository\SubscriptionRepository;
use App\Repository\TagRepository;
use App\Repository\TradeRepository;
use App\Repository\Unmanaged\AbstractUnmanagedRepository;
use App\Repository\Unmanaged\FieldEdgeRepository;
use App\Repository\Unmanaged\GenericIngestRepository;
use App\Repository\Unmanaged\HubPlusRepository;
use App\Repository\Unmanaged\ServiceTitanRepository;
use App\Repository\Unmanaged\SuccesswareRepository;
use App\ValueObjects\CompanyObject;
use Doctrine\ORM\EntityManagerInterface;
use App\ValueObjects\InvoiceObject;
use App\ValueObjects\ProspectObject;
use Exception;
use Psr\Log\LoggerInterface;

use function App\Functions\app_getSanitizedHeaderValue;

class DataStreamDigestingService
{
    private const STREAM_RECORD_BATCH_SIZE = 1000;
    private bool $deleteRemote = false;
    private int $limit = 0;
    private array $toDelete = [ ];
    private array $tagsCache = [];
    private array $tradesCache = [];
    private ?Company $contextCompany = null;
    private array $newProspectMap = [];
    private array $newProspectDetailsMap = [];
    private array $newInvoiceMap = [];

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly CompanyRepository $companyRepository,
        private readonly CustomerRepository $customerRepository,
        private readonly ProspectRepository $prospectRepository,
        private readonly InvoiceRepository $invoiceRepository,
        private readonly FieldEdgeRepository $fieldEdgeRepository,
        private readonly GenericIngestRepository $genericIngestRepository,
        private readonly ServiceTitanRepository $serviceTitanRepository,
        private readonly SuccesswareRepository $successwareRepository,
        private readonly HubPlusRepository $hubPlusRepository,
        private readonly TradeRepository $tradeRepository,
        private readonly TagRepository $tagRepository,
        private readonly SubscriptionRepository $subscriptionRepository,
        private readonly ProspectDetailsRepository $prospectDetailsRepository,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function setDeleteRemote(bool $bool): static
    {
        $this->deleteRemote = $bool;
        return $this;
    }

    public function setLimit(int $limit): static
    {
        $this->limit = $limit;
        return $this;
    }

    /**
     * @throws Exception
     */
    public function syncSources(?Company $company = null): void
    {
        $this->contextCompany = $company;
        $this->syncGenericIngestDatabase();
    }

    /**
     * @throws Exception
     */
    public function syncGenericIngestDatabase($skipStatusTracking = false): bool
    {
        $this->logger->info('Context Company: ' . $this->contextCompany?->getIdentifier());
        $this->logger->info('Syncing Generic Ingest Database');
        $this->importRemoteData(
            $this->genericIngestRepository,
            GenericIngestProspectsStreamProspectParser::class,
            Prospect::class,
            'prospects_stream'
        );

        $this->logger->info(
            'Deleting Stream Data',
            [
                'table' => 'prospects_stream',
            ]
        );
        if ($this->deleteRemote) {
            $this->deleteRemote(
                $this->genericIngestRepository,
                'prospects_stream'
            );
        }
        $this->flushAndClearEntityManager(
            'prospects_stream'
        );

        if (!$skipStatusTracking) {
            $this->setHubPlusImportProgress('prospects_stream');
        }

        $this->importRemoteData(
            $this->genericIngestRepository,
            GenericIngestMembersStreamProspectParser::class,
            Prospect::class,
            'members_stream'
        );

        $this->logger->info(
            'Deleting Stream Data',
            [
                'table' => 'members_stream',
            ]
        );
        if ($this->deleteRemote) {
            $this->deleteRemote(
                $this->genericIngestRepository,
                'members_stream'
            );
        }
        $this->flushAndClearEntityManager(
            'members_stream'
        );

        if (!$skipStatusTracking) {
            $this->setHubPlusImportProgress('members_stream');
        }
        $this->importRemoteData(
            $this->genericIngestRepository,
            GenericIngestInvoicesStreamProspectParser::class,
            Prospect::class,
            'invoices_stream'
        );

        $this->importRemoteData(
            $this->genericIngestRepository,
            GenericIngestInvoicesStreamInvoiceParser::class,
            Invoice::class,
            'invoices_stream'
        );

        $this->logger->info(
            'Deleting Stream Data',
            [
                'table' => 'invoices_stream',
            ]
        );

        if ($this->deleteRemote) {
            $this->deleteRemote(
                $this->genericIngestRepository,
                'invoices_stream'
            );
        }
        $this->flushAndClearEntityManager(
            'invoices_stream'
        );

        if (!$skipStatusTracking) {
            $this->setHubPlusImportProgress('invoices_stream');
        }

        return true;
    }

    /**
     * @throws Exception
     */
    public function syncFieldEdgeIngestDatabase(): bool
    {
        $this->importRemoteData(
            $this->fieldEdgeRepository,
            FieldEdgeProspectParser::class,
            Prospect::class,
            'invoices_stream'
        );

        $this->importRemoteData(
            $this->fieldEdgeRepository,
            FieldEdgeInvoiceParser::class,
            Invoice::class,
            'invoices_stream'
        );

        if ($this->deleteRemote) {
            $this->deleteRemote(
                $this->fieldEdgeRepository,
                'invoices_stream'
            );
        }

        return true;
    }

    /**
     * @throws Exception
     */
    public function syncServiceTitanIngestDatabase(): bool
    {
        // Create Prospects from the members_stream
        $this->importRemoteData(
            $this->serviceTitanRepository,
            ServiceTitanMembersStreamProspectParser::class,
            Prospect::class,
            'members_stream'
        );

        if ($this->deleteRemote) {
            $this->deleteRemote(
                $this->serviceTitanRepository,
                'members_stream'
            );
        }

        // Create Prospects from the invoices_stream
        $this->importRemoteData(
            $this->serviceTitanRepository,
            ServiceTitanInvoicesStreamProspectParser::class,
            Prospect::class,
            'invoices_stream'
        );

        // Create Invoices from the invoices_stream
        $this->importRemoteData(
            $this->serviceTitanRepository,
            ServiceTitanInvoicesStreamInvoiceParser::class,
            Invoice::class,
            'invoices_stream'
        );

        if ($this->deleteRemote) {
            $this->deleteRemote(
                $this->serviceTitanRepository,
                'invoices_stream'
            );
        }

        return true;
    }

    /**
     * @throws Exception
     */
    public function syncSuccesswareIngestDatabase(): bool
    {
        $this->importRemoteData(
            $this->successwareRepository,
            SuccesswareProspectParser::class,
            Prospect::class,
            'invoices_stream'
        );

        if ($this->deleteRemote) {
            $this->deleteRemote(
                $this->successwareRepository,
                'invoices_stream'
            );
        }

        return true;
    }

    private function fetchGroupedResults(
        AbstractUnmanagedRepository $unmanagedRepository,
        string $remoteTable
    ): array {
        $groupedResults = [ ];
        if (
            $db = $unmanagedRepository->getDatabase()
        ) {
            $query = $db->createQueryBuilder()
                ->select('*')
                ->from($remoteTable)
            ;

            if ($this->contextCompany) {
                $query->andWhere('tenant = :tenant')
                    ->setParameter('tenant', $this->contextCompany->getIdentifier());
            }

            if ($this->limit > 0) {
                $query->setMaxResults($this->limit);
            }

            try {
                $results = $query->executeQuery()->fetchAllAssociative();
            } catch (Exception) {
                $results = [ ];
            }

            foreach ($results as $result) {
                if (!empty($result['tenant'])) {
                    $groupedResults[$result['tenant']][] = $result;
                }
            }
        }

        return $groupedResults;
    }

    /**
     * @throws Exception
     */
    private function importRemoteData(
        AbstractUnmanagedRepository $unmanagedRepository,
        string $parserClass,
        string $entityClass,
        string $remoteTable,
    ): void {
        $this->logger->info(sprintf(
            'Importing %s from %s.%s.%s',
            $entityClass,
            $unmanagedRepository->getDatabaseUrl(),
            $remoteTable,
            $parserClass
        ));
        $groupedResults = $this->fetchGroupedResults(
            $unmanagedRepository,
            $remoteTable
        );

        foreach ($groupedResults as $tenantId => $results) {
            // Reset the company entity, and add it to the persistence queue
            $company = $this->companyRepository->findActiveByIdentifierOrCreate(
                Company::getIdentifierFromExtId($tenantId)
            );

            if (!$company) {
                continue;
            }

            $this->entityManager->persist($company);

            $this->logger->info(sprintf(
                'Processing %s records for company %s from %s.%s',
                count($results),
                $company->getIdentifier(),
                $unmanagedRepository->getDatabaseUrl(),
                $remoteTable
            ));

            if ($this->weAreProcessingMembersStream($remoteTable)) {
                $this->logger->info(sprintf(
                    'Resetting Customer records as inactive for company %s',
                    $company->getIdentifier()
                ));
                $this->resetCustomerRecordsAsInactive($company);
                $this->logger->info(sprintf(
                    'Deleting all Subscriptions for company %s',
                    $company->getIdentifier()
                ));
                $this->deleteAllSubscriptionsForCompany($company);
            }

            /** @var AbstractParser $parser */
            $parser = new $parserClass(CompanyObject::fromEntity($company));
            $headers = array_map(
                static function ($str) {
                    return app_getSanitizedHeaderValue($str);
                },
                array_keys($results[0] ?? [])
            );

            $results = array_map(static function ($item) use ($headers) {
                return array_combine($headers, $item);
            }, $results);

            $parser->parse(
                $headers,
                $results
            );

            $count = 0;
            $this->toDelete = [ ];

            $this->logger->info(sprintf(
                'Parsed %s records for company %s from %s.%s',
                count($parser->getRecords()),
                $company->getIdentifier(),
                $unmanagedRepository->getDatabaseUrl(),
                $remoteTable
            ));

            foreach ($parser->getRecords() as $_object) {
                // Refresh the company entity if it's not being managed
                if (!$this->entityManager->contains($company)) {
                    $company = $this->companyRepository->find($company->getId());
                    $this->entityManager->persist($company);
                }

                if ($count % 100 === 0) {
                    $this->logger->info(sprintf(
                        'Processing %s %s for company %s from %s.%s',
                        $count,
                        $entityClass,
                        $company->getIdentifier(),
                        $unmanagedRepository->getDatabaseUrl(),
                        $remoteTable
                    ));
                }

                // Process ProspectObjects
                if (
                    $entityClass === Prospect::class &&
                    $_object instanceof ProspectObject
                ) {
                    $this->saveProspectObject(
                        $_object,
                        $company,
                        $parserClass,
                        $remoteTable
                    );
                }

                // Process InvoiceObjects
                if (
                    $entityClass === Invoice::class &&
                    $_object instanceof InvoiceObject &&
                    $_object->prospect instanceof ProspectObject
                ) {
                    $this->saveInvoiceObject(
                        $_object,
                        $company,
                        $parserClass
                    );
                }

                try {
                    if ($count % self::STREAM_RECORD_BATCH_SIZE === 0) {
                        $this->logger->info(sprintf(
                            'Ingested and Flushing %s %s for company %s from %s.%s',
                            $count,
                            $entityClass,
                            $company->getIdentifier(),
                            $unmanagedRepository->getDatabaseUrl(),
                            $remoteTable
                        ));
                        $this->flushManagedEntities();
                    }

                    if (
                        !empty($_object->_extra['id'])
                    ) {
                        $this->toDelete[] = $_object->_extra['id'];
                    }

                    $count++;
                } catch (Exception $e) {
                    $this->logger->warning(
                        $e->getMessage(),
                        [
                            'entity' => $entityClass,
                            'company' => $company->getIdentifier(),
                            'remoteTable' => $remoteTable,
                            'parser' => $parserClass,
                            'object' => $_object->_extra['id'],
                        ]
                    );
                }
            }

            $this->logger->info(
                'Flushing remaining managed entities'
            );
            $this->flushManagedEntities();

            app_logger(__FUNCTION__)->info(sprintf(
                'Ingested %s %s for company %s from %s.%s',
                $count,
                $entityClass,
                $company->getIdentifier(),
                $unmanagedRepository->getDatabaseUrl(),
                $remoteTable
            ));
        }
    }

    /**
     * @throws Exception
     */
    private function flushManagedEntities(): void
    {
        app_logger(__FUNCTION__)->info(sprintf(
            'Managing %s entities',
            $this->getManagedEntityCount()
        ));
        try {
            $this->flushAndClearEntityManager(
                'From flushManagedEntities'
            );
        } catch (Exception $e) {
            $this->logger->warning(
                'Error in flushManagedEntities(): '
                .
                $e->getMessage()
            );
            //remove the last STREAM_RECORD_BATCH_SIZE objects from $this->toDelete
            $this->logger->warning(
                'Removing last ' . self::STREAM_RECORD_BATCH_SIZE . ' objects from $this->toDelete'
            );
            $this->toDelete = array_slice(
                $this->toDelete,
                0,
                -self::STREAM_RECORD_BATCH_SIZE
            );
            //intentionally NOT rethrowing the Exception
        }
    }

    private function getManagedEntityCount(): int
    {
        return (int) array_sum(array_map('count', $this->entityManager->getUnitOfWork()->getIdentityMap()));
    }

    private function deleteRemote(
        AbstractUnmanagedRepository $repository,
        string $remoteTable
    ): void {
        app_logger(__FUNCTION__)->info(sprintf(
            'Deleting %s records from %s.%s',
            count($this->toDelete),
            $repository->getDatabaseUrl(),
            $remoteTable
        ));
        $repository->deleteById($remoteTable, $this->toDelete);
        $this->toDelete = [ ];
    }

    private function fetchProspectFromObject(ProspectObject $prospectObject, Company $company): ?Prospect
    {
        return $this->prospectRepository->findOneBy([
            'company' => $company,
            'externalId' => $prospectObject->externalId,
        ]);
    }

    private function saveProspectObject(
        ProspectObject $prospectObject,
        Company $company,
        string $source = __CLASS__,
        string $remoteTable = null
    ): void {
        $prospect = $this->fetchProspectFromObject(
            $prospectObject,
            $company
        );

        $company = $this->companyRepository->findOneById($company->getId());

        if (!$prospect instanceof Prospect) {
            if (isset($this->newProspectMap[$prospectObject->externalId])) {
                $prospect = $this->newProspectMap[$prospectObject->externalId];
            } else {
                $prospect = (new Prospect())->fromValueObject($prospectObject);
                $this->newProspectMap[$prospectObject->externalId] = $prospect;
            }
        }

        $prospect->setCompany($company);

        // Create/fetch an associated Customer, depending on the stream being processed
        $customer = $prospect->getCustomer();

        if (
            $this->shouldWeCreateCustomersForProspects($remoteTable)
            &&
            !$customer
        ) {
            $customer = new Customer();
        }

        if ($customer) {
            if ($this->shouldWeMarkCustomerAsActive($remoteTable)) {
                $customer->setActive(true);
                $customer->setVersion($prospectObject->version);
            }
            $customer->setCompany($company);
            $customer->setProspect($prospect);

            if (!$customer->getId()) {
                $prospectFullName = $prospect->getFullName();
                if (empty($prospectFullName)) {
                    $prospectFullName = 'Imported Customer';
                }
                $customer->setName(
                    $prospectFullName
                );
            }
        }

        $prospectDetails = $this->prospectDetailsRepository->findOneByProspect(
            $prospect
        );
        if (!$prospectDetails) {
            //from chris: it's important to only setProspect()
            //if the ProspectDetails is new
            if (isset($this->newProspectDetailsMap[$prospectObject->externalId])) {
                $prospectDetails = $this->newProspectDetailsMap[$prospectObject->externalId];
            } else {
                $prospectDetails = new ProspectDetails();
                $prospectDetails->setProspect($prospect);
                $this->newProspectDetailsMap[$prospectObject->externalId] = $prospectDetails;
            }
        }
        if (!empty($prospectObject->_extra['networthprem'])) {
            $prospectDetails->setInfoBase($prospectObject->_extra['networthprem']);
        }
        if (!empty($prospectObject->_extra['yearhomebuilt'])) {
            $prospectDetails->setYearBuilt($prospectObject->_extra['yearhomebuilt']);
        }
        if (!empty($prospectObject->_extra['ageofindividual'])) {
            $prospectDetails->setAge($prospectObject->_extra['ageofindividual']);
        }
        if (!empty($prospectObject->_extra['estincome'])) {
            $prospectDetails->setEstimatedIncome($prospectObject->_extra['estincome']);
        }

        $prospectSource = $prospect->getProspectSourceByName($source);
        $prospectSource->setCurrentJson($prospectObject->toJson());

        $versionTags = [];
        // Create/fetch tags
        if (!empty($prospectObject->_extra['version'])) {
            $versionTag = $this->createTag(
                $company,
                sprintf("v.%s", $prospectObject->_extra['version']),
            );
            $versionTag->setSystem(true);
            $versionTag->setDescription('Auto-generated version tag');
            $prospect->addTag($versionTag);
            $versionTags[] = $versionTag;
        }

        $customTags = [];
        if (!empty($prospectObject->_extra['tag'])) {
            $tags = explode(',', $prospectObject->_extra['tag']);
            foreach ($tags as $tag) {
                $customTag = $this->createTag(
                    $company,
                    sprintf("%s", $tag),
                );
                $prospect->addTag($customTag);
                $customTags[] = $customTag;
            }
        }

        $subscription = null;
        if (
            $customer &&
            $this->weAreProcessingMembersStream($remoteTable)
        ) {
            $subscription = $this->processSubscriptions(
                $customer,
                $prospectObject,
                $company
            );

            if ($subscription) {
                $this->logger->info(sprintf(
                    'Adding Subscription %s for Customer %s',
                    $subscription->getName(),
                    $customer->getId(),
                ), [
                    'prospect' => $prospect->getFullName(),
                    'customer' => $customer->getId(),
                ]);
                $customer->addSubscription($subscription);
            }
        }

        $this->entityManager->persist($company);

        $this->entityManager->persist($prospectDetails);
        $this->entityManager->persist($prospectSource);

        foreach ($versionTags as $versionTag) { //for Prospect
            $this->entityManager->persist($versionTag);
        }
        foreach ($customTags as $customTag) { //for Prospect
            $this->entityManager->persist($customTag);
        }
        if ($subscription) { //for Customer
            $this->entityManager->persist($subscription);
        }
        if ($customer) {
            $this->entityManager->persist($customer);
        }
        $this->entityManager->persist($prospect);
    }

    private function createTag(
        Company $company,
        string $tagName
    ): Tag {
        $tagKey = sprintf(
            '%s.%s',
            $company->getIdentifier(),
            $tagName
        );

        if (isset($this->tagsCache[$tagKey])) {
            $tag = $this->tagsCache[$tagKey];
        } else {
            $tag = $this->tagRepository->findOneByCompanyAndTagName(
                $company,
                $tagName
            ) ?? new Tag();
        }

        $tag->setCompany($company);
        $tag->setName($tagName);

        $this->tagsCache[$tagKey] = $tag;

        return $tag;
    }

    private function saveInvoiceObject(
        InvoiceObject $_object,
        Company $company,
        string $source = __CLASS__
    ): void {
        // Create/fetch a Prospect
        $prospect = $this->fetchProspectFromObject(
            $_object->prospect,
            $company
        );

        if (
            !$prospect instanceof Prospect ||
            !$prospect->getCustomer() instanceof Customer
        ) {
            return;
        }

        $this->entityManager->persist($prospect);

        $invoice = $this->invoiceRepository->resolveInvoice(
            $company,
            $prospect,
            $_object,
        );

        if ($invoice->isNew()) {
            if (isset($this->newInvoiceMap[$invoice->getExternalId()])) {
                //override with pre-existing invoice
                $invoice = $this->newInvoiceMap[$invoice->getExternalId()];
            } else {
                //track this new invoice
                $this->newInvoiceMap[$invoice->getExternalId()] = $invoice;
            }
            $invoice = $invoice->fromValueObject($_object);
        }

        $invoice
            ->setCompany($company)
            ->setCustomer($prospect->getCustomer())
            ->setExternalId($_object->getKey());
        if ($tradeName = $_object->tradeName) {
            $trade =
                $this->findTrade($tradeName);
            if ($trade) {
                $this->entityManager->persist($trade);
                $invoice->setTrade($trade);
            }
        }

        $this->entityManager->persist($invoice);
    }

    private function findTrade(
        string $tradeName,
    ): Trade {
        $tradeKey = sprintf(
            '%s',
            $tradeName
        );

        if (isset($this->tradesCache[$tradeKey])) {
            $trade = $this->tradesCache[$tradeKey];
        } else {
            $trade = $this->tradeRepository->findByName(
                $tradeName,
            );
        }

        if ($trade) {
            $this->tradesCache[$tradeKey] = $trade;
        }

        return $trade;
    }

    private function weAreProcessingMembersStream(string $remoteTable): bool
    {
        return $remoteTable === 'members_stream';
    }

    private function weAreProcessingInvoicesStream(string $remoteTable): bool
    {
        return $remoteTable === 'invoices_stream';
    }

    private function weAreProcessingProspectsStream(string $remoteTable): bool
    {
        return $remoteTable === 'prospects_stream';
    }

    private function shouldWeMarkCustomerAsActive(string $remoteTable): bool
    {
        return $this->weAreProcessingMembersStream($remoteTable);
    }

    private function shouldWeCreateCustomersForProspects(string $remoteTable): bool
    {
        if ($this->weAreProcessingMembersStream($remoteTable)) {
            return true;
        }

        if ($this->weAreProcessingInvoicesStream($remoteTable)) {
            return true;
        }

        if ($this->weAreProcessingProspectsStream($remoteTable)) {
            return false;
        }

        return false;
    }

    private function processSubscriptions(
        Customer $customer,
        ProspectObject $prospectObject,
        Company $company
    ): ?Subscription {
        if ($prospectObject->isActiveMembership) {
            $subscription = new Subscription();
            $subscription->setCompany($company);
            $subscription->setCustomer($customer);
            $subscription->setActive(true);
            $subscription->setName('Imported Membership');
            $subscription->setExternalId($prospectObject->externalId);

            return $subscription;
        }

        return null;
    }

    /**
     * @param Company $company
     * @return void
     * @throws \Doctrine\DBAL\Exception
     */
    private function resetCustomerRecordsAsInactive(Company $company): void
    {
        $currentCustomerVersion = $this->genericIngestRepository->getLatestMemberVersionForCompany(
            $company
        );
        $this->logger->info(
            'Resetting every Customer as inactive for company ' . $company->getIdentifier(),
            [
                'currentCustomerVersion' => $currentCustomerVersion,
            ]
        );
        if (!$currentCustomerVersion) {
            return;
        }
        $this->customerRepository->resetEveryCustomerAsInactiveExcludingCustomerVersion(
            $company,
            $currentCustomerVersion
        );
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    private function deleteAllSubscriptionsForCompany(Company $company): void
    {
        $currentCustomerVersion = $this->genericIngestRepository->getLatestMemberVersionForCompany(
            $company
        );
        if (!$currentCustomerVersion) {
            return;
        }
        $this->logger->info(
            'Deleting all Subscriptions for company ' . $company->getIdentifier(),
            [
                'currentCustomerVersion' => $currentCustomerVersion,
            ]
        );
        $this->subscriptionRepository->deleteAllSubscriptionsForCompanyExcludingCustomerVersion(
            $company,
            $currentCustomerVersion
        );
    }

    private function flushAndClearEntityManager(
        string $streamName
    ): void {
        $this->logger->info(
            $streamName
            .
            ': Flushing and Clearing Entity Manager'
        );
        if ($this->entityManager->isOpen()) {
            $this->entityManager->flush();
            $this->entityManager->clear();
            $this->newProspectMap = [];
            $this->newProspectDetailsMap = [];
            $this->tagsCache = [];
            $this->tradesCache = [];
            $this->newInvoiceMap = [];
        } else {
            $this->logger->warning(
                'Did not Flush() and Clear() because EntityManager is closed'
            );
        }
    }

    private function setHubPlusImportProgress(string $table): void
    {
        try {
            $tenant = $this->contextCompany?->getIdentifier();

            // Get the in-progress jobs
            $jobs = $this->hubPlusRepository->getInProgressImportJobsForTenant($tenant, $table);

            foreach ($jobs as $job) {
                $importId = (int) $job['id'];

                $remaining = $this->genericIngestRepository
                    ->getRowCountForHubPlusImportId($table, $tenant, $importId);

                $this->hubPlusRepository->setImportProgress($importId, $remaining);
            }
        } catch (Exception $e) {
            $this->logger->warning($e->getMessage());
        }
    }
}
