<?php

namespace App\Controller\API\Company;

use App\Controller\API\ApiController;
use App\DTO\Domain\ProspectFilterRulesDTO;
use App\Exceptions\NotFoundException\CompanyNotFoundException;
use App\Exceptions\NotFoundException\LocationNotFoundException;
use App\Exceptions\NotFoundException\ProspectFilterRuleNotFoundException;
use App\Repository\CompanyRepository;
use App\Services\ProspectAggregatedService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;

class GetCompanyProspectsAggregatedController extends ApiController
{
    public function __construct(
        private readonly CompanyRepository $companyRepository,
        private readonly ProspectAggregatedService $aggregatedProspectsService,
    ) {
    }

    /**
     * @throws CompanyNotFoundException
     * @throws LocationNotFoundException
     * @throws ProspectFilterRuleNotFoundException
     */
    #[Route(
        '/api/company/aggregated-prospects',
        name: 'api_aggregated_prospects_get',
        methods: ['GET']
    )]
    public function __invoke(
        Request $request,
        #[MapQueryString] ProspectFilterRulesDTO $filterRulesDTO = null,
    ): Response {

        $filterRulesDTO ??= new ProspectFilterRulesDTO();
        $tags = array_filter(
            explode(',', $request->query->get('tagsRule'))
        );
        $filterRulesDTO->tags = $tags;

        $this->companyRepository->findOneByIdentifierOrFail($filterRulesDTO->intacctId);
        $prospectsAggregatedData = $this->aggregatedProspectsService->getProspectsAggregatedData($filterRulesDTO);

        return $this->createJsonSuccessResponse($prospectsAggregatedData);
    }
}
