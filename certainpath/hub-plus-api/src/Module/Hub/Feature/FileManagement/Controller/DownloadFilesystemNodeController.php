<?php

declare(strict_types=1);

namespace App\Module\Hub\Feature\FileManagement\Controller;

use App\Controller\ApiController;
use App\Entity\File;
use App\Module\Hub\Feature\FileManagement\Service\DownloadFilesystemNodeService;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/private')]
class DownloadFilesystemNodeController extends ApiController
{
    public function __construct(
        private readonly DownloadFilesystemNodeService $downloadFilesystemNodeService,
    ) {
    }

    #[Route('/file-manager/file/{uuid}/download', name: 'api_file_manager_download', methods: ['GET'])]
    public function __invoke(File $file): StreamedResponse
    {
        return $this->downloadFilesystemNodeService->downloadFile($file);
    }
}
