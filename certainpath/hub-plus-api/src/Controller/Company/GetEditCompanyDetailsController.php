<?php

declare(strict_types=1);

namespace App\Controller\Company;

use App\Controller\ApiController;
use App\Entity\Company;
use App\Security\Voter\CompanyVoter;
use App\Service\Company\GetEditCompanyDetailsService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/private')]
class GetEditCompanyDetailsController extends ApiController
{
    public function __construct(
        private readonly GetEditCompanyDetailsService $getEditCompanyDetailsService,
    ) {
    }

    #[Route('/edit-company-details/{uuid}', name: 'api_company_get_edit_details', methods: ['GET'])]
    public function __invoke(Company $company): Response
    {
        $this->denyAccessUnlessGranted(CompanyVoter::EDIT, $company);
        $companyData = $this->getEditCompanyDetailsService->getEditCompanyDetails($company);

        return $this->createSuccessResponse($companyData);
    }
}
