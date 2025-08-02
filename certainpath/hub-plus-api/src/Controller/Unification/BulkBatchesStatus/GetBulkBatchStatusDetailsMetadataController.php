<?php

declare(strict_types=1);

namespace App\Controller\Unification\BulkBatchesStatus;

use App\Controller\ApiController;
use App\DTO\Request\GetBulkBatchStatusQueryDTO;
use App\Exception\APICommunicationException;
use App\Exception\BulkBatchStatusDetailsMetadataNotFoundException;
use App\Service\Unification\GetBulkBatchStatusDetailsMetadataService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;

#[Route(path: '/api/private')]
class GetBulkBatchStatusDetailsMetadataController extends ApiController
{
    public function __construct(
        private readonly GetBulkBatchStatusDetailsMetadataService $detailsMetadataService,
    ) {
    }

    /**
     * @throws ServerExceptionInterface
     * @throws ClientExceptionInterface
     * @throws APICommunicationException
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws BulkBatchStatusDetailsMetadataNotFoundException
     */
    #[Route(
        '/details-metadata/bulk-batch-status',
        name: 'api_details_metadata_bulk_batch_status_get',
        methods: ['GET']
    )]
    public function __invoke(
        #[MapQueryString] GetBulkBatchStatusQueryDTO $dto,
    ): Response {
        $detailsMetadata = $this->detailsMetadataService->getDetailsMetadata(
            $dto->year,
            $dto->week,
        );

        return $this->createSuccessResponse($detailsMetadata);
    }
}
