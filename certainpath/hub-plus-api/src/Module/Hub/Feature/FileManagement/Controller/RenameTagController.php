<?php

declare(strict_types=1);

namespace App\Module\Hub\Feature\FileManagement\Controller;

use App\Controller\ApiController;
use App\Module\Hub\Feature\FileManagement\DTO\Request\RenameTagRequestDTO;
use App\Module\Hub\Feature\FileManagement\Service\RenameTagService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/private/file-management')]
class RenameTagController extends ApiController
{
    public function __construct(
        private readonly RenameTagService $renameTagService,
    ) {
    }

    #[Route('/tags/{id}/rename', name: 'api_file_management_rename_tag', methods: ['PATCH'])]
    public function __invoke(
        int $id,
        #[MapRequestPayload] RenameTagRequestDTO $requestDTO,
    ): Response {
        $response = $this->renameTagService->renameTag($id, $requestDTO);

        return $this->createSuccessResponse($response);
    }
}
