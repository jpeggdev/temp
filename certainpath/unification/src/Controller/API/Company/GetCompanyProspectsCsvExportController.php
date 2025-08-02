<?php

namespace App\Controller\API\Company;

use App\Controller\API\ApiController;
use App\DTO\Domain\ProspectFilterRulesDTO;
use App\DTO\Query\Prospect\ProspectExportMetadataDTO;
use App\Exceptions\NotFoundException\CompanyNotFoundException;
use App\Exceptions\NotFoundException\ProspectFilterRuleNotFoundException;
use App\Generator\CompanyProspectsCsvGenerator;
use App\Repository\CompanyRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;

class GetCompanyProspectsCsvExportController extends ApiController
{
    public function __construct(
        private readonly CompanyRepository $companyRepository,
        private readonly CompanyProspectsCsvGenerator $companyProspectsCsvGenerator
    ) {
    }

    /**
     * @throws CompanyNotFoundException
     * @throws ProspectFilterRuleNotFoundException
     */
    #[Route('/api/company/prospects/export/csv', name: 'api_company_prospects_export_csv_get', methods: ['GET'])]
    public function __invoke(
        #[MapQueryString] ProspectFilterRulesDTO $filterRulesDTO = new ProspectFilterRulesDTO(),
        #[MapQueryString] ProspectExportMetadataDTO $exportMetadataQueryDTO = new ProspectExportMetadataDTO(),
    ): Response {
        $this->companyRepository->findOneByIdentifierOrFail($filterRulesDTO->intacctId);
        $fileName = $this->prepareFileName($filterRulesDTO->intacctId, $filterRulesDTO);
        $csvGenerator = $this->companyProspectsCsvGenerator->createGenerator($filterRulesDTO, $exportMetadataQueryDTO);

        return $this->createCsvStreamedResponse(
            $fileName,
            $csvGenerator,
        );
    }

    private function prepareFileName(
        string $intacctId,
        ProspectFilterRulesDTO $filterRulesDTO
    ): string {
        $timestamp = date('Y-m-d-H:i:s');
        $filePrefix = $filterRulesDTO->customerInclusionRule;
        return sprintf(
            '%s_%s_%s.csv',
            $intacctId,
            $filePrefix,
            $timestamp
        );
    }
}
