<?php

namespace App\Tests\Generator;

use App\DTO\Query\Batch\BatchesProspectsCsvExportDTO;
use App\Entity\Company;
use App\Exceptions\DomainException\Campaign\CampaignAlreadyCreatedException;
use App\Exceptions\DomainException\Campaign\CampaignAlreadyProcessingException;
use App\Exceptions\DomainException\Campaign\CampaignCreationFailedException;
use App\Exceptions\NotFoundException\BatchStatusNotFoundException;
use App\Exceptions\NotFoundException\CampaignStatusNotFoundException;
use App\Exceptions\NotFoundException\CompanyNotFoundException;
use App\Generator\BatchesProspectsCsvGenerator;
use App\Services\ProspectExportService;
use App\Tests\FunctionalTestCase;
use Carbon\Carbon;
use Doctrine\ORM\Exception\ORMException;

class BatchesProspectsCsvGeneratorTest extends FunctionalTestCase
{
    private BatchesProspectsCsvGenerator $generator;
    private ProspectExportService $prospectExportService;

    private Company $company;

    public function setUp(): void
    {
        parent::setUp();
        $this->generator = $this->getService(BatchesProspectsCsvGenerator::class);
        $this->prospectExportService = $this->getService(ProspectExportService::class);
        $this->company = $this->initializeCompany();
    }

    /**
     * @throws ORMException
     * @throws CompanyNotFoundException
     * @throws BatchStatusNotFoundException
     * @throws CampaignCreationFailedException
     * @throws CampaignStatusNotFoundException
     * @throws CampaignAlreadyCreatedException
     * @throws CampaignAlreadyProcessingException
     */
    public function testCreateGeneratorSuccess(): void
    {
        $prospectFilterRulesDTO = $this->prepareProspectFilterRulesDTO($this->company->getIdentifier());
        $createCampaignDTO = $this->prepareCreateCampaignDTO(prospectFilterRules: $prospectFilterRulesDTO);
        $this->initializeCampaignAsync($createCampaignDTO);

        $carbonDate = Carbon::createFromFormat('Y-m-d', $createCampaignDTO->startDate);
        $calendarWeek = $carbonDate->weekOfYear();
        $calendarYear = $carbonDate->year;
        $exportDto = new BatchesProspectsCsvExportDTO($calendarWeek, $calendarYear);

        $totalRows = 0;
        $generator = $this->generator->createGenerator($exportDto);

        foreach ($generator as $key => $row) {
            if ($key === 0) {
                $headers = $this->prospectExportService->getHeaders();
                $this->assertSame($headers, $row);
            }

            $totalRows++;
            $this->assertIsArray($row);
        }

        // 1 header row + 3 prospect data rows
        $this->assertEquals(4, $totalRows);
    }
}
