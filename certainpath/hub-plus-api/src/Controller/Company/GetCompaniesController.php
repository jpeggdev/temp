<?php

declare(strict_types=1);

// region Init

namespace App\Controller\Company;

use App\Controller\ApiController;
use App\DTO\Request\Company\CompanyQueryDTO;
use App\Security\Voter\CompanyVoter;
use App\Service\Company\CompanyQueryService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;

// endregion

#[Route(path: '/api/private')]
class GetCompaniesController extends ApiController
{
    // region Declarations
    public function __construct(private readonly CompanyQueryService $companyQueryService)
    {
    }
    // endregion

    // region __invoke
    #[Route('/companies', name: 'api_companies_get', methods: ['GET'])]
    public function __invoke(
        #[MapQueryString] CompanyQueryDTO $queryDto,
        Request $request,
    ): Response {
        $this->denyAccessUnlessGranted(CompanyVoter::VIEW_ALL);
        $companiesData = $this->companyQueryService->getCompanies(
            $queryDto
        );

        return $this->createSuccessResponse(
            $companiesData,
            $companiesData['totalCount']
        );
    }
    // endregion
}
