<?php

declare(strict_types=1);

namespace App\Controller\Company;

use App\Controller\ApiController;
use App\DTO\Request\Company\EditCompanyDTO;
use App\Entity\Company;
use App\Service\Company\EditCompanyService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/private')]
class EditCompanyController extends ApiController
{
    public function __construct(private readonly EditCompanyService $editCompanyService)
    {
    }

    #[Route('/companies/{uuid}/edit', name: 'api_company_edit', methods: ['PUT'])]
    public function __invoke(Company $company, #[MapRequestPayload] EditCompanyDTO $editCompanyDTO): Response
    {
        $updatedCompany = $this->editCompanyService->editCompany($company, $editCompanyDTO);

        return $this->createSuccessResponse($updatedCompany);
    }
}
