<?php

declare(strict_types=1);

namespace App\Module\Hub\Feature\FileManagement\Controller;

use App\Controller\ApiController;
use App\Module\Hub\Feature\FileManagement\DTO\Request\BulkDeleteNodesRequestDTO;
use App\Module\Hub\Feature\FileManagement\Service\BulkDeleteFilesystemNodesService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/private/file-management')]
class DeleteMultipleFilesystemNodesController extends ApiController
{
    public function __construct(
        private readonly BulkDeleteFilesystemNodesService $bulkDeleteService,
    ) {
    }

    #[Route(
        '/nodes/bulk-delete',
        name: 'api_file_management_delete_multiple_nodes',
        methods: ['POST']
    )]
    public function __invoke(
        #[MapRequestPayload] BulkDeleteNodesRequestDTO $requestDTO,
    ): Response {
        $response = $this->bulkDeleteService->queueNodesForDeletion($requestDTO->uuids);

        return $this->createSuccessResponse($response);
    }
}
