<?php

declare(strict_types=1);

namespace App\Controller\Unification\BulkBatchesStatus;

use App\Controller\ApiController;
use App\DTO\Request\GetBulkBatchStatusQueryDTO;
use App\Exception\APICommunicationException;
use App\Exception\BulkBatchStatusEventNotFoundException;
use App\Service\Unification\GetBulkBatchStatusEventsService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;

#[Route(path: '/api/private')]
class GetBulkBatchStatusEventsController extends ApiController
{
    public function __construct(
        private readonly GetBulkBatchStatusEventsService $getBulkBatchStatusEventsService,
    ) {
    }

    /**
     * @throws ServerExceptionInterface
     * @throws ClientExceptionInterface
     * @throws APICommunicationException
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws BulkBatchStatusEventNotFoundException
     */
    #[Route('/bulk-batch-status-events', name: 'api_bulk_batch_status_events_get', methods: ['GET'])]
    public function __invoke(
        #[MapQueryString] GetBulkBatchStatusQueryDTO $queryDTO,
    ): Response {
        $bulkBatchStatusEvents = $this->getBulkBatchStatusEventsService->getBulkBatchStatusEvents(
            year: $queryDTO->year,
            week: $queryDTO->week,
            sortOrder: $queryDTO->sortOrder
        );

        return $this->createSuccessResponse(
            $bulkBatchStatusEvents['batchStatusBulkEvents']
        );
    }
}
