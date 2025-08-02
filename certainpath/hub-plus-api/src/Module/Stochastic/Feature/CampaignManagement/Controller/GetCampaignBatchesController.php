<?php

declare(strict_types=1);

namespace App\Module\Stochastic\Feature\CampaignManagement\Controller;

use App\Controller\ApiController;
use App\DTO\Request\GetCampaignBatchesQueryDTO;
use App\Exception\APICommunicationException;
use App\Service\Unification\GetCampaignBatchesService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;

#[Route(path: '/api/private')]
class GetCampaignBatchesController extends ApiController
{
    public function __construct(
        private readonly GetCampaignBatchesService $getCampaignBatchesService,
    ) {
    }

    /**
     * @throws APICommunicationException
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    #[Route('/campaign/{id}/batches', name: 'api_campaign_batches_get', methods: ['GET'])]
    public function __invoke(
        int $id,
        #[MapQueryString] GetCampaignBatchesQueryDTO $queryDto,
    ): Response {
        $batchesResponse = $this->getCampaignBatchesService->getCampaignBatches(
            $id,
            $queryDto->page,
            $queryDto->perPage,
            strtoupper($queryDto->sortOrder),
            $queryDto->batchStatusId
        );

        return $this->createSuccessResponse(
            $batchesResponse['batches'],
            $batchesResponse['totalCount']
        );
    }
}
