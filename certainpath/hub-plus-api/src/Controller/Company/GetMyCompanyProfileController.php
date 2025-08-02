<?php

declare(strict_types=1);

namespace App\Controller\Company;

use App\Controller\ApiController;
use App\DTO\LoggedInUserDTO;
use App\Service\Company\GetMyCompanyProfileService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/private')]
class GetMyCompanyProfileController extends ApiController
{
    public function __construct(
        private readonly GetMyCompanyProfileService $getMyCompanyProfileService,
    ) {
    }

    #[Route('/my-company-profile', name: 'api_company_get_my_profile', methods: ['GET'])]
    public function __invoke(LoggedInUserDTO $loggedInUserDTO): Response
    {
        $companyData = $this->getMyCompanyProfileService->getMyCompanyProfile($loggedInUserDTO);

        return $this->createSuccessResponse($companyData);
    }
}
