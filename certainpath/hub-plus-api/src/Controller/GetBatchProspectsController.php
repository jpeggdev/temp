<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\Request\GetBatchProspectsQueryDTO;
use App\Service\Unification\GetBatchProspectsService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/private')]
class GetBatchProspectsController extends ApiController
{
    public function __construct(private readonly GetBatchProspectsService $getBatchProspectsService)
    {
    }

    #[Route('/batch/{id}/prospects', name: 'api_batch_prospects_get', methods: ['GET'])]
    public function __invoke(
        int $id,
        #[MapQueryString] GetBatchProspectsQueryDTO $getBatchProspectsQueryDTO,
    ): Response {
        $prospectsResponse = $this->getBatchProspectsService->getBatchProspects(
            $id,
            $getBatchProspectsQueryDTO->page,
            $getBatchProspectsQueryDTO->perPage,
            strtoupper($getBatchProspectsQueryDTO->sortOrder)
        );

        return $this->createSuccessResponse(
            $prospectsResponse['prospects'],
            $prospectsResponse['totalCount']
        );
    }
}
