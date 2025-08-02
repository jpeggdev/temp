<?php

declare(strict_types=1);

namespace App\Controller\Company;

use App\Controller\ApiController;
use App\DTO\LoggedInUserDTO;
use App\DTO\Request\Company\CreateCompanyDTO;
use App\Service\Company\CreateCompanyService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/private')]
class CreateCompanyController extends ApiController
{
    public function __construct(
        private readonly CreateCompanyService $createCompanyService,
    ) {
    }

    #[Route('/companies/create', name: 'api_company_create', methods: ['POST'])]
    public function __invoke(
        #[MapRequestPayload] CreateCompanyDTO $createCompanyDTO,
        LoggedInUserDTO $loggedInUserDTO,
    ): Response {
        $company = $this->createCompanyService->createCompany(
            $createCompanyDTO,
            $loggedInUserDTO->getActiveCompany()
        );

        return $this->createSuccessResponse($company);
    }
}
