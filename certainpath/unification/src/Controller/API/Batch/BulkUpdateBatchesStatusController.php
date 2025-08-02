<?php

namespace App\Controller\API\Batch;

use App\Controller\API\ApiController;
use App\DTO\Request\Batch\BulkUpdateBatchesStatusDTO;
use App\Exceptions\DomainException\Batch\BulkUpdateBatchesStatusesCannotBeCompletedException;
use App\Exceptions\NotFoundException\BatchStatusNotFoundException;
use App\Services\BulkUpdateBatchesStatusService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

class BulkUpdateBatchesStatusController extends ApiController
{
    public function __construct(
        private readonly BulkUpdateBatchesStatusService $bulkUpdateBatchesStatusService,
    ) {
    }

    /**
     * @throws BatchStatusNotFoundException
     * @throws BulkUpdateBatchesStatusesCannotBeCompletedException
     */
    #[Route('/api/batches/bulk-update-status', name: 'api_batches_bulk_update_status', methods: ['PATCH'])]
    public function __invoke(
        #[MapRequestPayload] BulkUpdateBatchesStatusDTO $dto = new BulkUpdateBatchesStatusDTO(),
    ): Response {
        $updatedBatches = $this->bulkUpdateBatchesStatusService->bulkUpdateStatus(
            $dto->year,
            $dto->week,
            $dto->status
        );

        return $this->createJsonSuccessResponse($updatedBatches);
    }
}
