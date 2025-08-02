<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\LoggedInUserDTO;
use App\DTO\Request\FieldServiceExportQueryDTO;
use App\Service\FieldServiceExportQueryService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/private')]
class GetFieldServiceExportsController extends ApiController
{
    public function __construct(private readonly FieldServiceExportQueryService $exportQueryService)
    {
    }

    #[Route('/field-service-exports', name: 'api_field_service_exports_get', methods: ['GET'])]
    public function __invoke(
        #[MapQueryString] FieldServiceExportQueryDTO $queryDto,
        LoggedInUserDTO $loggedInUserDTO,
    ): Response {
        $exportsData = $this->exportQueryService->getExports(
            $loggedInUserDTO->getActiveCompany(),
            $queryDto
        );

        return $this->createSuccessResponse(
            $exportsData['exports'],
            $exportsData['totalCount']
        );
    }
}
