<?php

declare(strict_types=1);

namespace App\Module\Hub\Feature\FileManagement\Controller;

use App\Controller\ApiController;
use App\Module\Hub\Feature\FileManagement\DTO\Request\RenameFilesystemNodeRequestDTO;
use App\Module\Hub\Feature\FileManagement\Service\RenameFilesystemNodeService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/private/file-management')]
class RenameFilesystemNodeController extends ApiController
{
    public function __construct(
        private readonly RenameFilesystemNodeService $renameFilesystemNodeService,
    ) {
    }

    #[Route(
        '/nodes/{uuid}/rename',
        name: 'api_file_management_rename_node',
        requirements: ['uuid' => '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}'],
        methods: ['PATCH']
    )]
    public function __invoke(
        string $uuid,
        #[MapRequestPayload] RenameFilesystemNodeRequestDTO $requestDTO,
    ): Response {
        $nodeResponse = $this->renameFilesystemNodeService->renameNode($uuid, $requestDTO);

        return $this->createSuccessResponse($nodeResponse);
    }
}
