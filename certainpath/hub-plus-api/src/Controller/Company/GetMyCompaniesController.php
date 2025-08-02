<?php

declare(strict_types=1);

namespace App\Controller\Company;

use App\Controller\ApiController;
use App\DTO\LoggedInUserDTO;
use App\DTO\Request\GetMyCompaniesQueryDTO;
use App\Service\Company\GetMyCompaniesService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/private')]
class GetMyCompaniesController extends ApiController
{
    public function __construct(private readonly GetMyCompaniesService $getMyCompaniesService)
    {
    }

    #[Route('/my-companies', name: 'api_my_companies_get', methods: ['GET'])]
    public function __invoke(
        LoggedInUserDTO $loggedInUserDTO,
        #[MapQueryString] GetMyCompaniesQueryDTO $query,
    ): Response {
        $companies = $this->getMyCompaniesService->getMyCompanies(
            $loggedInUserDTO,
            $query->page,
            $query->limit,
            $query->search
        );

        return $this->createSuccessResponse($companies);
    }
}
