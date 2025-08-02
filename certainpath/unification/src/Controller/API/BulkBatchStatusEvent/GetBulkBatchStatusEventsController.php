<?php

namespace App\Controller\API\BulkBatchStatusEvent;

use App\Controller\API\ApiController;
use App\DTO\Query\BatchStatusStatusBulkEvent\BulkBatchStatusEventsQueryDTO;
use App\Repository\BulkBatchStatusEventRepository;
use App\Resources\BulkBatchStatusEventResource;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;

class GetBulkBatchStatusEventsController extends ApiController
{
    public function __construct(
        private readonly BulkBatchStatusEventRepository $bulkBatchStatusEventRepository,
        private readonly BulkBatchStatusEventResource $bulkBatchStatusEventResource
    ) {
    }

    #[Route('/api/bulk-batch-status-events', name: 'api_bulk_batch_status_events_get', methods: ['GET'])]
    public function __invoke(
        #[MapQueryString] BulkBatchStatusEventsQueryDTO $dto = new BulkBatchStatusEventsQueryDTO(),
    ): Response {
        $batchStatusBulkEvents = $this->bulkBatchStatusEventRepository->findAllByYearAndWeek(
            $dto->year,
            $dto->week,
            $dto->sortOrder
        );
        $batchStatusBulkEventsData = $this->bulkBatchStatusEventResource->transformCollection($batchStatusBulkEvents);

        return $this->createJsonSuccessResponse($batchStatusBulkEventsData);
    }
}
