<?php

declare(strict_types=1);

namespace App\Module\Hub\Feature\FileManagement\Controller;

use App\Controller\ApiController;
use App\Module\Hub\Feature\FileManagement\DTO\Request\BulkDownloadNodesRequestDTO;
use App\Module\Hub\Feature\FileManagement\Service\BulkDownloadFilesystemNodesService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/private')]
class DownloadMultipleFilesystemNodesController extends ApiController
{
    public function __construct(
        private readonly BulkDownloadFilesystemNodesService $bulkDownloadService,
    ) {
    }

    #[Route('/file-manager/nodes/download', name: 'api_file_manager_download_multiple', methods: ['POST'])]
    public function __invoke(
        #[MapRequestPayload] BulkDownloadNodesRequestDTO $requestDTO,
    ): StreamedResponse|Response {
        return $this->bulkDownloadService->downloadNodes($requestDTO->uuids);
    }
}
