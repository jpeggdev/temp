<?php

declare(strict_types=1);

namespace App\Controller\File;

use App\Controller\ApiController;
use App\Service\File\UploadTmpFileService;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/api/private')]
class UploadTmpFileController extends ApiController
{
    public function __construct(
        private readonly UploadTmpFileService $uploadTmpFileService,
    ) {
    }

    #[Route('/tmp/file-upload', name: 'api_private_tmp_file_upload', methods: ['POST'])]
    public function __invoke(Request $request): Response
    {
        /** @var UploadedFile|null $file */
        $file = $request->files->get('file');
        if (!$file) {
            return $this->json(['error' => 'No file provided'], Response::HTTP_BAD_REQUEST);
        }

        $bucketName = $request->request->get('bucketName');
        $folderName = $request->request->get('folderName');

        $uploadDto = $this->uploadTmpFileService->uploadTempFile($file, $bucketName, $folderName);

        return $this->createSuccessResponse($uploadDto);
    }
}
