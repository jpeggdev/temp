<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\Request\UpdateCompanyFieldServiceSoftwareDTO;
use App\Entity\Company;
use App\Service\UpdateCompanyFieldServiceSoftwareService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/private')]
class UpdateCompanyFieldServiceSoftwareController extends ApiController
{
    public function __construct(
        private readonly UpdateCompanyFieldServiceSoftwareService $updateFieldServiceSoftwareService,
    ) {
    }

    #[Route(
        '/companies/{uuid}/field-service-software',
        name: 'api_company_update_field_service_software',
        methods: ['PUT']
    )]
    public function __invoke(Company $company, #[MapRequestPayload] UpdateCompanyFieldServiceSoftwareDTO $dto): Response
    {
        $updatedCompany = $this->updateFieldServiceSoftwareService->updateFieldServiceSoftware($company, $dto);

        return $this->createSuccessResponse($updatedCompany);
    }
}
