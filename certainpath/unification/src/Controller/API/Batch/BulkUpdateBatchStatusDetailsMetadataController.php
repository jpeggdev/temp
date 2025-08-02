<?php

namespace App\Controller\API\Batch;

use App\Controller\API\ApiController;
use App\DTO\Query\Batch\GetBulkBatchStatusDetailsMetadataDTO;
use App\Services\DetailsMetadata\GetBulkUpdateBatchStatusDetailsMetadataService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;

class BulkUpdateBatchStatusDetailsMetadataController extends ApiController
{
    public function __construct(
        private readonly GetBulkUpdateBatchStatusDetailsMetadataService $detailsMetadataService,
    ) {
    }

    #[Route(
        '/api/details-metadata/batch/bulk-update-status',
        name: 'api_details_metadata_batch_bulk_update_status',
        methods: ['GET']
    )]
    public function __invoke(
        #[MapQueryString] GetBulkBatchStatusDetailsMetadataDTO $dto,
    ): Response {
        $detailsMetadata = $this->detailsMetadataService->getDetailMetadata(
            $dto->year,
            $dto->week,
        );

        return $this->createJsonSuccessResponse($detailsMetadata);
    }
}
