<?php

declare(strict_types=1);

namespace App\Module\Hub\Feature\FileManagement\Controller;

use App\Controller\ApiController;
use App\DTO\LoggedInUserDTO;
use App\Module\Hub\Feature\FileManagement\Service\UploadFilesystemNodesService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/private')]
class UploadFilesystemNodesController extends ApiController
{
    public function __construct(
        private readonly UploadFilesystemNodesService $uploadFilesystemNodesService,
    ) {
    }

    #[Route('/file-manager/upload', name: 'api_private_file_manager_upload', methods: ['POST'])]
    public function __invoke(Request $request, LoggedInUserDTO $loggedInUserDTO): Response
    {
        $files = $request->files->get('files');
        if (!$files) {
            return $this->json(['error' => 'No files provided'], Response::HTTP_BAD_REQUEST);
        }

        $folderUuid = $request->request->get('folderUuid');

        $uploadResult = $this->uploadFilesystemNodesService->uploadFiles(
            $files,
            $folderUuid,
            $loggedInUserDTO->getActiveCompany()->getIntacctId(),
        );

        return $this->createSuccessResponse($uploadResult);
    }
}
