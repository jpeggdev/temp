<?php

declare(strict_types=1);

namespace App\Module\Hub\Feature\FileManagement\Controller;

use App\Controller\ApiController;
use App\Module\Hub\Feature\FileManagement\DTO\Request\CreateFolderRequestDTO;
use App\Module\Hub\Feature\FileManagement\Service\CreateFolderService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/private/file-management')]
class CreateFolderController extends ApiController
{
    public function __construct(
        private readonly CreateFolderService $createFolderService,
    ) {
    }

    #[Route('/folders', name: 'api_file_management_create_folder', methods: ['POST'])]
    public function __invoke(
        #[MapRequestPayload] CreateFolderRequestDTO $requestDTO,
    ): Response {
        $folderResponse = $this->createFolderService->createFolder($requestDTO);

        return $this->createSuccessResponse($folderResponse);
    }
}
