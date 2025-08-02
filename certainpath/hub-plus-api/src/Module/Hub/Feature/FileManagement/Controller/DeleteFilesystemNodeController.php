<?php

declare(strict_types=1);

namespace App\Module\Hub\Feature\FileManagement\Controller;

use App\Controller\ApiController;
use App\Module\Hub\Feature\FileManagement\Service\DeleteFilesystemNodeService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/private/file-management')]
class DeleteFilesystemNodeController extends ApiController
{
    public function __construct(
        private readonly DeleteFilesystemNodeService $deleteFilesystemNodeService,
    ) {
    }

    #[Route(
        '/nodes/{uuid}',
        name: 'api_file_management_delete_node',
        requirements: ['uuid' => '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}'],
        methods: ['DELETE']
    )]
    public function __invoke(string $uuid): Response
    {
        $this->deleteFilesystemNodeService->deleteNode($uuid);

        return $this->createSuccessResponse(['success' => true]);
    }
}
