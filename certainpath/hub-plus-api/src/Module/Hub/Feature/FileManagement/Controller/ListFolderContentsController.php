<?php

declare(strict_types=1);

namespace App\Module\Hub\Feature\FileManagement\Controller;

use App\Controller\ApiController;
use App\Module\Hub\Feature\FileManagement\DTO\Request\ListFolderContentsRequestDTO;
use App\Module\Hub\Feature\FileManagement\Service\ListFolderContentsService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/private/file-management')]
class ListFolderContentsController extends ApiController
{
    public function __construct(
        private readonly ListFolderContentsService $listFolderContentsService,
    ) {
    }

    #[Route('/folders/contents', name: 'api_file_management_list_folder_contents', methods: ['GET'])]
    public function __invoke(
        #[MapQueryString] ListFolderContentsRequestDTO $requestDTO,
    ): Response {
        $result = $this->listFolderContentsService->listContents($requestDTO);

        return $this->createSuccessResponse(
            $result['data'],
            $result['total'],
            $result['hasMore']
        );
    }
}
