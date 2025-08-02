<?php

namespace App\Controller\API\Prospects;

use App\Controller\API\ApiController;
use App\DTO\Query\Prospect\ProspectQueryDTO;
use App\Services\ProspectQueryService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;

class GetProspectsController extends ApiController
{
    public function __construct(private readonly ProspectQueryService $prospectQueryService)
    {
    }

    #[Route('/api/prospects', name: 'api_prospects_get', methods: ['GET'])]
    public function __invoke(
        #[MapQueryString] ProspectQueryDTO $queryDto,
        Request $request,
    ): Response {
        $prospectsData = $this->prospectQueryService->getProspects(
            $queryDto
        );

        return $this->createJsonSuccessResponse(
            $prospectsData['prospects'],
            $prospectsData
        );
    }

}