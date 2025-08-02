<?php

namespace App\Tests\Generator;

use App\Entity\Campaign;
use App\Exceptions\DomainException\Campaign\CampaignAlreadyCreatedException;
use App\Exceptions\DomainException\Campaign\CampaignAlreadyProcessingException;
use App\Exceptions\DomainException\Campaign\CampaignCreationFailedException;
use App\Exceptions\NotFoundException\CampaignStatusNotFoundException;
use App\Exceptions\NotFoundException\CompanyNotFoundException;
use App\Generator\BatchProspectsCsvGenerator;
use App\Services\ProspectExportService;
use App\Tests\FunctionalTestCase;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\Exception\ORMException;

class BatchProspectsCsvGeneratorTest extends FunctionalTestCase
{
    private Campaign $campaign;
    private BatchProspectsCsvGenerator $generator;
    private ProspectExportService $prospectExportService;

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

        $company = $this->initializeCompany();
        $mailPackage = $this->initializeMailPackage();
        $prospectFilterRulesDTO = $this->prepareProspectFilterRulesDTO($company->getIdentifier());
        $createCampaignDTO = $this->prepareCreateCampaignDTO(
            company: $company,
            mailPackage: $mailPackage,
            prospectFilterRules: $prospectFilterRulesDTO
        );

        $this->campaign = $this->initializeCampaignAsync($createCampaignDTO);
        $this->generator = $this->getBatchProspectsCsvGenerator();
        $this->prospectExportService = $this->getService(ProspectExportService::class);
    }

    public function testCreateGeneratorSuccess(): void
    {
        $batches = $this->campaign->getBatches();
        $batch = $batches->first();

        $jobNumber = $this->faker->randomNumber();
        $ringTo = $this->faker->phoneNumber();
        $exportDto = $this->prepareExportMetadataDTO(
            $jobNumber,
            $ringTo
        );

        $totalRows = 0;
        $generator = $this->generator->createGenerator($batch, $exportDto);

        foreach ($generator as $key => $row) {
            if ($key === 0) {
                $headers = $this->prospectExportService->getHeaders();
                $this->assertSame($headers, $row);
            } else {
                $this->assertEquals($jobNumber, $row[0]);
                $this->assertEquals($ringTo, $row[1]);
                $this->assertEquals($this->campaign->getCompany()->getIdentifier(), $row[4]);
            }

            $totalRows++;
            $this->assertIsArray($row);
        }

        // 1 header row + 3 prospect data rows
        $this->assertEquals(4, $totalRows);
    }
}
