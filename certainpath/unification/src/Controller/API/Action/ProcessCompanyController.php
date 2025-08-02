<?php

namespace App\Controller\API\Action;

use App\Controller\API\ApiController;
use App\Services\CompanyDigestingAndProcessingService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ProcessCompanyController extends ApiController
{
    public function __construct(
        private readonly CompanyDigestingAndProcessingService $processor,
    ) {
    }

    #[Route(
        '/api/company/process/{companyIdentifier}',
        name: 'api_company_process',
        methods: ['GET']
    )]
    public function __invoke(
        string $companyIdentifier
    ): Response {
        $this->processor->dispatchCompanyProcessingByIdentifier(
            $companyIdentifier
        );
        return $this->createJsonSuccessResponse(
            [
                'message' => 'Company processing message dispatched',
                'company' => $companyIdentifier
            ]
        );
    }
}
