<?php

namespace App\Controller\API\Company;

use App\Controller\API\ApiController;
use App\DTO\Domain\ProspectFilterRulesDTO;
use App\Exceptions\NotFoundException\CompanyNotFoundException;
use App\Exceptions\NotFoundException\ProspectFilterRuleNotFoundException;
use App\Repository\CompanyRepository;
use App\Repository\ProspectRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;

class GetCompanyProspectsCountController extends ApiController
{
    public function __construct(
        private readonly CompanyRepository $companyRepository,
        private readonly ProspectRepository $prospectRepository,
    ) {
    }

    /**
     * @throws CompanyNotFoundException
     * @throws ProspectFilterRuleNotFoundException
     */
    #[Route(
        '/api/company/prospects/count',
        name: 'api_company_prospects_count',
        methods: ['GET']
    )]
    public function __invoke(
        #[MapQueryString] ProspectFilterRulesDTO $filterRulesDTO = new ProspectFilterRulesDTO(),
    ): Response {
        $this->companyRepository->findOneByIdentifierOrFail($filterRulesDTO->intacctId);
        $prospectsCount = $this->prospectRepository->getCountByProspectFilterRulesDTO($filterRulesDTO);

        return $this->createJsonSuccessResponse($prospectsCount);
    }

}