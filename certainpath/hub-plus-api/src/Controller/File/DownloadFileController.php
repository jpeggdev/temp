<?php

declare(strict_types=1);

namespace App\Controller\File;

use App\Controller\ApiController;
use App\Entity\File;
use App\Service\File\DownloadFileService;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/private')]
class DownloadFileController extends ApiController
{
    public function __construct(
        private readonly DownloadFileService $downloadFileService,
    ) {
    }

    #[Route('/file/{uuid}/download', name: 'api_file_download', methods: ['GET'])]
    public function __invoke(File $file): StreamedResponse
    {
        return $this->downloadFileService->downloadFile($file);
    }
}
