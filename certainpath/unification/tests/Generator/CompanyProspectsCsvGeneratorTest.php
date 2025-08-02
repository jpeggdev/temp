<?php

namespace App\Tests\Generator;

use App\DTO\Domain\ProspectFilterRulesDTO;
use App\Entity\Campaign;
use App\Exceptions\DomainException\Campaign\CampaignAlreadyCreatedException;
use App\Exceptions\DomainException\Campaign\CampaignAlreadyProcessingException;
use App\Exceptions\DomainException\Campaign\CampaignCreationFailedException;
use App\Exceptions\NotFoundException\CampaignStatusNotFoundException;
use App\Exceptions\NotFoundException\CompanyNotFoundException;
use App\Exceptions\NotFoundException\ProspectFilterRuleNotFoundException;
use App\Generator\CompanyProspectsCsvGenerator;
use App\Services\ProspectExportService;
use App\Services\ProspectFilterRule\ProspectFilterRuleRegistry;
use App\Tests\FunctionalTestCase;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\Exception\ORMException;

class CompanyProspectsCsvGeneratorTest extends FunctionalTestCase
{
    private Campaign $campaign;
    private CompanyProspectsCsvGenerator $generator;
    private ProspectExportService $prospectExportService;
    private ProspectFilterRulesDTO $prospectFilterRuleDTO;

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

        $company = $this->initializeCompany();
        $mailPackage = $this->initializeMailPackage();
        $prospectFilterRulesDTO = $this->prepareProspectFilterRulesDTO(
            $company->getIdentifier(),
            ProspectFilterRuleRegistry::INCLUDE_PROSPECTS_AND_CUSTOMERS_VALUE
        );
        $createCampaignDTO = $this->prepareCreateCampaignDTO(
            company: $company,
            mailPackage: $mailPackage,
            prospectFilterRules: $prospectFilterRulesDTO
        );

        $this->campaign = $this->initializeCampaignAsync($createCampaignDTO);
        $this->generator = $this->getCompanyProspectsCsvGenerator();
        $this->prospectFilterRuleDTO = $prospectFilterRulesDTO;
        $this->prospectExportService = $this->getService(ProspectExportService::class);
    }

    /**
     * @throws ProspectFilterRuleNotFoundException
     */
    public function testCreateGeneratorSuccess(): void
    {
        $jobNumber = $this->faker->randomNumber();
        $ringTo = $this->faker->phoneNumber();
        $exportDto = $this->prepareExportMetadataDTO(
            $jobNumber,
            $ringTo
        );

        $totalRows = 0;
        $generator = $this->generator->createGenerator($this->prospectFilterRuleDTO, $exportDto);

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

        // 1 header row + 20 prospect data rows
        $this->assertEquals(21, $totalRows);
    }
}
