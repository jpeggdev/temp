<?php

declare(strict_types=1);

namespace App\Controller\Company;

use App\Controller\ApiController;
use App\Service\Company\GetMyCompanyService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/private')]
class GetMyCompanyController extends ApiController
{
    private GetMyCompanyService $getMyCompanyService;

    public function __construct(GetMyCompanyService $getMyCompanyService)
    {
        $this->getMyCompanyService = $getMyCompanyService;
    }

    #[Route('/my-company', name: 'api_company_get', methods: ['GET'])]
    public function __invoke(): Response
    {
        $companyResponse = $this->getMyCompanyService->getMyCompanyDetails();

        return $this->createSuccessResponse($companyResponse);
    }
}
