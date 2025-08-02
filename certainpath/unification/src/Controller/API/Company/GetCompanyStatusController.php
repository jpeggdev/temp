<?php

namespace App\Controller\API\Company;

use App\Controller\API\ApiController;
use App\Exceptions\NotFoundException\CompanyNotFoundException;
use App\Services\CompanyStatusService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class GetCompanyStatusController extends ApiController
{
    public function __construct(
        private readonly CompanyStatusService $companyStatusService,
    ) {
    }

    /**
     * @throws CompanyNotFoundException
     */
    #[Route('/api/company/{identifier}/status', name: 'api_company_status_get', methods: ['GET'])]
    public function __invoke(
        string $identifier,
    ): Response {
        return $this->createJsonSuccessResponse(
            $this->companyStatusService->getCompanyStatusByIdentifier(
                $identifier
            )
        );
    }
}
