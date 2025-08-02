<?php

declare(strict_types=1);

namespace App\Tests;

use App\Client\FileClient;
use App\Client\SalesforceClient;
use App\Client\UnificationClient;
use App\DTO\Request\Company\CompanyQueryDTO;
use App\DTO\Request\Company\CreateCompanyDTO;
use App\Entity\BatchPostage;
use App\Entity\BusinessRole;
use App\Entity\Company;
use App\Entity\Employee;
use App\Exception\CouldNotReadSheet;
use App\Exception\ExcelFileIsCorrupted;
use App\Exception\FieldsAreMissing;
use App\Exception\NoFilePathWasProvided;
use App\Exception\UnsupportedFileTypeException;
use App\Module\Stochastic\Feature\PostageUploads\Repository\BatchPostageRepository;
use App\Module\Stochastic\Feature\PostageUploads\Service\UploadPostageExpenseService;
use App\Repository\BusinessRoleRepository;
use App\Repository\CampaignProductRepository;
use App\Repository\CompanyRepository;
use App\Repository\EmployeeRepository;
use App\Repository\External\IngestRepository;
use App\Repository\FieldServiceSoftwareRepository;
use App\Repository\TradeRepository;
use App\Service\Company\CompanyQueryService;
use App\Service\Company\CompanyRosterService;
use App\Service\Company\CompanyRosterSyncService;
use App\Service\Company\CreateCompanyService;
use App\Service\Company\EditCompanyService;
use App\Service\ExcelAnalyzerService;
use App\Service\FieldServicesUploadService;
use App\Service\ProspectSourceUploadService;
use App\Service\SalesforceRosterService;
use App\Service\StochasticCompanyIngestionService;
use App\Service\StochasticRosterLoaderService;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\SchemaTool;
use Faker\Factory;
use Faker\Generator;
use League\Csv\SyntaxError;
use League\Csv\UnavailableStream;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Liip\TestFixturesBundle\Services\DatabaseTools\AbstractDatabaseTool;
use OpenSpout\Common\Exception\IOException;
use OpenSpout\Reader\Exception\ReaderNotOpenedException;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

abstract class AbstractKernelTestCase extends KernelTestCase
{
    protected AbstractDatabaseTool $databaseTool;
    protected EntityManager $entityManager;
    protected Generator $faker;
    protected CompanyRepository $companyRepository;
    protected CampaignProductRepository $campaignProductRepository;
    protected IngestRepository $ingestRepository;
    protected TradeRepository $tradeRepository;
    protected FieldServiceSoftwareRepository $fieldServiceSoftwareRepository;
    protected BusinessRoleRepository $businessRoleRepository;
    protected EmployeeRepository $employeeRepository;
    protected BatchPostageRepository $batchPostageRepository;

    protected bool $doInitializeProducts = false;
    protected bool $doInitializeTrades = false;
    protected bool $doInitializeSoftware = false;
    protected bool $doInitializeBusinessRoles = false;

    /**
     * @throws CouldNotReadSheet
     * @throws ExcelFileIsCorrupted
     * @throws Exception
     * @throws FieldsAreMissing
     * @throws IOException
     * @throws NoFilePathWasProvided
     * @throws ReaderNotOpenedException
     * @throws SyntaxError
     * @throws UnavailableStream
     * @throws UnsupportedFileTypeException
     * @throws \League\Csv\Exception
     */
    public function setUp(): void
    {
        self::bootKernel();
        $container = static::getContainer();
        $this->entityManager = $container->get('doctrine')->getManager();

        // Ensure we have a fresh connection
        $connection = $this->entityManager->getConnection();
        if (!$connection->isConnected()) {
            $connection->connect();
        }

        $databaseName = $connection->getDatabase();
        self::assertSame(
            'hubplus_test',
            $databaseName
        );
        $this->faker = Factory::create();
        $this->databaseTool = self::getContainer()->get(DatabaseToolCollection::class)->get();
        $schemaTool = new SchemaTool($this->entityManager);
        $metadata = $this->entityManager->getMetadataFactory()->getAllMetadata();
        try {
            $schemaTool->dropSchema($metadata);
            $schemaTool->createSchema($metadata);
        } catch (\Throwable $e) {
            // If schema operations fail, ensure connection is still closed properly
            if ($connection->isConnected()) {
                $connection->close();
            }
            throw $e;
        }
        $this->bootstrapRepos();
        $this->bootstrapData();
    }

    protected function tearDown(): void
    {
        // Close the EntityManager and its connection
        if (isset($this->entityManager)) {
            $connection = $this->entityManager->getConnection();
            if ($connection->isConnected()) {
                $connection->close();
            }
            $this->entityManager->close();
            unset($this->entityManager);
        }

        parent::tearDown();
        unset($this->databaseTool);
    }

    protected function getService(string $serviceClass): ?object
    {
        try {
            return self::getContainer()->get($serviceClass);
        } catch (\Exception $e) {
            echo 'Could not instantiate: '.$serviceClass;
            echo $e->getMessage();

            return null;
        }
    }

    /**
     * @template T of object
     * @param class-string<T> $serviceClass
     * @return object|null
     */

    protected function getRepository(string $serviceClass): ?object
    {
        return $this->getService($serviceClass);
    }

    protected function getEntityManager(): EntityManager
    {
        return $this->entityManager;
    }

    protected function getStochasticCompanyIngestionService(): StochasticCompanyIngestionService
    {
        return $this->getService(StochasticCompanyIngestionService::class);
    }

    protected function getStochasticRosterLoaderService(): StochasticRosterLoaderService
    {
        return $this->getService(StochasticRosterLoaderService::class);
    }

    protected function getFieldServicesUploadService(): FieldServicesUploadService
    {
        return $this->getService(FieldServicesUploadService::class);
    }

    protected function getProspectSourceUploadService(): ProspectSourceUploadService
    {
        return $this->getService(ProspectSourceUploadService::class);
    }

    protected function getFileClient(): FileClient
    {
        return $this->getService(FileClient::class);
    }

    protected function getUnificationClient(): UnificationClient
    {
        return $this->getService(UnificationClient::class);
    }

    protected function getExcelAnalyzerService(): ExcelAnalyzerService
    {
        return $this->getService(ExcelAnalyzerService::class);
    }

    protected function getSalesforceClient(): SalesforceClient
    {
        return $this->getService(SalesforceClient::class);
    }

    protected function getSalesforceRosterService(): SalesforceRosterService
    {
        return $this->getService(SalesforceRosterService::class);
    }

    protected function getCompanyRosterService(): CompanyRosterService
    {
        return $this->getService(CompanyRosterService::class);
    }

    protected function getCompanyRosterSyncService(): CompanyRosterSyncService
    {
        return $this->getService(CompanyRosterSyncService::class);
    }

    protected function getCompanyQueryService(): CompanyQueryService
    {
        return $this->getService(CompanyQueryService::class);
    }

    protected function getCreateCompanyService(): CreateCompanyService
    {
        return $this->getService(CreateCompanyService::class);
    }

    protected function getEditCompanyService(): EditCompanyService
    {
        return $this->getService(EditCompanyService::class);
    }

    protected function getUploadPostageExpenseService(): UploadPostageExpenseService
    {
        return $this->getService(UploadPostageExpenseService::class);
    }

    protected function debugString(string $string): void
    {
        echo $string.PHP_EOL;
    }

    /**
     * @throws \JsonException
     */
    protected function debug(mixed $object): void
    {
        $encodedObject = json_encode($object, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT);
        $this->debugString($encodedObject);
    }

    /**
     * @throws \JsonException
     */
    protected function assertObjectMatchesJsonFile(mixed $objectToVerify, string $filePath): void
    {
        self::assertFileExists($filePath);
        $jsonStringFromFile = file_get_contents($filePath);
        $jsonStringObjectToVerify = json_encode($objectToVerify, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT);
        //                file_put_contents(
        //                    $filePath,
        //                    $jsonStringObjectToVerify
        //                );
        self::assertSame(
            $jsonStringFromFile,
            $jsonStringObjectToVerify
        );
    }

    /**
     * @throws \Exception
     */
    protected function getTestCompany(): Company
    {
        $intacctId = 'CPA2';
        $companyName = $this->faker->company();
        $companyToCreate = new CreateCompanyDTO(
            $companyName,
            $this->faker->url(),
            $this->faker->uuid(),
            $intacctId,
            $this->faker->email()
        );
        $createdCompany = $this->getCreateCompanyService()->createCompany(
            $companyToCreate
        );

        self::assertNotNull($createdCompany);

        $companiesFound = $this->getCompanyQueryService()->getCompanies(
            new CompanyQueryDTO(
                $companyName,
            )
        );

        self::assertCount(1, $companiesFound['companies']);

        $foundCreatedCompany = $this->companyRepository->findOneByIdentifier($intacctId);

        self::assertNotNull($foundCreatedCompany);

        self::assertSame(
            $intacctId,
            $foundCreatedCompany->getIntacctId()
        );
        return $foundCreatedCompany;
    }

    private function getCampaignProductRepository(): CampaignProductRepository
    {
        return $this->getService(
            CampaignProductRepository::class
        );
    }

    private function getIngestRepository(): IngestRepository
    {
        return $this->getService(
            IngestRepository::class
        );
    }

    private function getTradeRepository(): TradeRepository
    {
        return $this->getService(
            TradeRepository::class
        );
    }

    private function getFieldServiceSoftwareRepository(): FieldServiceSoftwareRepository
    {
        return $this->getService(
            FieldServiceSoftwareRepository::class
        );
    }

    /**
     * @throws Exception
     * @throws CouldNotReadSheet
     * @throws ExcelFileIsCorrupted
     * @throws FieldsAreMissing
     * @throws NoFilePathWasProvided
     * @throws UnsupportedFileTypeException
     * @throws \League\Csv\Exception
     * @throws SyntaxError
     * @throws UnavailableStream
     * @throws IOException
     * @throws ReaderNotOpenedException
     */
    private function bootstrapRepos(): void
    {
        /** @var CompanyRepository $companyRepository */
        $companyRepository = $this->entityManager->getRepository(Company::class);
        $this->companyRepository = $companyRepository;
        $this->ingestRepository = $this->getIngestRepository();
        if ($this->ingestRepository->isLocalDatabase()) {
            $this->ingestRepository->dropTables();
        }
        $this->ingestRepository->initializeTables();
        $this->tradeRepository = $this->getTradeRepository();
        $this->fieldServiceSoftwareRepository = $this->getFieldServiceSoftwareRepository();
        $this->campaignProductRepository = $this->getCampaignProductRepository();
        /** @var BusinessRoleRepository $businessRoleRepository */
        $businessRoleRepository = $this->entityManager->getRepository(BusinessRole::class);
        $this->businessRoleRepository = $businessRoleRepository;
        /** @var EmployeeRepository $employeeRepository */
        $employeeRepository = $this->entityManager->getRepository(Employee::class);
        $this->employeeRepository = $employeeRepository;
        /** @var BatchPostageRepository $batchPostageRepository */
        $batchPostageRepository = $this->entityManager->getRepository(BatchPostage::class);
        $this->batchPostageRepository = $batchPostageRepository;
    }

    /**
     * @throws IOException
     * @throws FieldsAreMissing
     * @throws UnsupportedFileTypeException
     * @throws ExcelFileIsCorrupted
     * @throws UnavailableStream
     * @throws ReaderNotOpenedException
     * @throws CouldNotReadSheet
     * @throws SyntaxError
     * @throws \League\Csv\Exception
     * @throws NoFilePathWasProvided
     */
    private function bootstrapData(): void
    {
        if ($this->doInitializeTrades) {
            $this->tradeRepository->initializeTrades();
        }
        if ($this->doInitializeSoftware) {
            $this->fieldServiceSoftwareRepository->initializeSoftware();
        }
        if ($this->doInitializeBusinessRoles) {
            $this->businessRoleRepository->initializeBusinessRoles();
        }
        if ($this->doInitializeProducts) {
            $this->campaignProductRepository->initializeCampaignProducts();
        }
    }
}
