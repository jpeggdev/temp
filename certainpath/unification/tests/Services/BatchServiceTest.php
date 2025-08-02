<?php

namespace App\Tests\Services;

use App\DTO\Request\Batch\PatchBatchDTO;
use App\DTO\Request\Campaign\CreateCampaignDTO;
use App\Entity\BatchStatus;
use App\Exceptions\DomainException\Campaign\CampaignAlreadyCreatedException;
use App\Exceptions\DomainException\Campaign\CampaignAlreadyProcessingException;
use App\Exceptions\DomainException\Campaign\CampaignCreationFailedException;
use App\Exceptions\NotFoundException\BatchStatusNotFoundException;
use App\Exceptions\NotFoundException\CampaignStatusNotFoundException;
use App\Exceptions\NotFoundException\CompanyNotFoundException;
use App\Services\BatchService;
use App\Tests\FunctionalTestCase;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\Exception\ORMException;

class BatchServiceTest extends FunctionalTestCase
{
    private BatchService $batchService;

    private CreateCampaignDTO $createCampaignDTO;

    /**
     * @throws ORMException
     * @throws Exception
     * @throws CompanyNotFoundException
     * @throws CampaignCreationFailedException
     * @throws CampaignStatusNotFoundException
     * @throws CampaignAlreadyCreatedException
     * @throws CampaignAlreadyProcessingException
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->batchService = $this->getService(BatchService::class);

        $company = $this->initializeCompany();
        $mailPackage = $this->initializeMailPackage();

        $prospectFilterRulesDTO = $this->prepareProspectFilterRulesDTO($company->getIdentifier());
        $this->createCampaignDTO = $this->prepareCreateCampaignDTO(
            company: $company,
            mailPackage: $mailPackage,
            prospectFilterRules: $prospectFilterRulesDTO
        );

        $this->initializeCampaignAsync($this->createCampaignDTO);
    }

    public function tearDown(): void
    {
        $this->entityManager->close();
        unset($this->faker);

        parent::tearDown();
    }

    /**
     * @throws BatchStatusNotFoundException
     */
    public function testPatchBatch(): void
    {
        $batchStatusRepository = $this->getBatchStatusRepository();
        $campaignRepository = $this->getCampaignRepository();

        $campaign = $campaignRepository->findOneByName($this->createCampaignDTO->name);

        $this->assertNotEmpty($campaign->getBatches());
        $batch = $campaign->getBatches()->first();

        $name = 'Modified Batch Name';
        $description = 'Modified Batch Description';
        $statusProcessed = $batchStatusRepository->findOneByName(BatchStatus::STATUS_PROCESSED);

        $this->assertNotEquals($batch->getName(), $name);
        $this->assertNotEquals($batch->getDescription(), $description);
        $this->assertNotEquals($batch->getBatchStatus()->getId(), $statusProcessed->getId());

        $dto = (new PatchBatchDTO())
            ->setName($name)
            ->setDescription($description)
            ->setBatchStatusId($statusProcessed->getId());


        $batch = $this->batchService->patchBatch($batch, $dto);

        $this->assertEquals($batch->getName(), $name);
        $this->assertEquals($batch->getDescription(), $description);
        $this->assertEquals($batch->getBatchStatus()->getId(), $statusProcessed->getId());
    }
}
