<?php

namespace App\Tests\Services\CampaignService;

use App\DTO\Request\Campaign\CreateCampaignDTO;
use App\DTO\Request\Campaign\PatchCampaignDTO;
use App\Exceptions\DomainException\Campaign\CampaignAlreadyCreatedException;
use App\Exceptions\DomainException\Campaign\CampaignAlreadyProcessingException;
use App\Exceptions\DomainException\Campaign\CampaignCreationFailedException;
use App\Exceptions\NotFoundException\BatchStatusNotFoundException;
use App\Exceptions\NotFoundException\CampaignStatusNotFoundException;
use App\Exceptions\NotFoundException\CompanyNotFoundException;
use App\Services\Campaign\PatchCampaignService;
use App\Tests\FunctionalTestCase;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\Exception\ORMException;

class PatchCampaignServiceTest extends FunctionalTestCase
{
    private PatchCampaignService $patchCampaignService;
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

        $this->patchCampaignService = $this->getPatchCampaignService();

        $mailPackage = $this->initializeMailPackage();
        $company = $this->initializeCompany();
        $this->dto = $this->prepareCreateCampaignDTO(company: $company, mailPackage: $mailPackage);
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
     * @throws BatchStatusNotFoundException
     */
    public function testPatchCampaign(): void
    {
        $campaignRepository = $this->getCampaignRepository();

        $campaign = $campaignRepository->findOneByName($this->dto->name);
        $campaignRepository->refreshCampaign($campaign);

        $name = 'Modified Campaign Name';
        $description = 'Modified Campaign Description';
        $phoneNumber = '222-333-4444';
        $status = '1';

        $this->assertNotEquals($name, $campaign->getName());
        $this->assertNotEquals($description, $campaign->getDescription());
        $this->assertNotEquals($phoneNumber, $campaign->getPhoneNumber());

        $dto = (new PatchCampaignDTO())
            ->setName($name)
            ->setDescription($description)
            ->setPhoneNumber($phoneNumber)
            ->setStatus($status);

        $campaign = $this->patchCampaignService->patchCampaign($campaign, $dto);

        $this->assertEquals($name, $campaign->getName());
        $this->assertEquals($description, $campaign->getDescription());
        $this->assertEquals($phoneNumber, $campaign->getPhoneNumber());
    }
}
