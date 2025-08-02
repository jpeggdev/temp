<?php

declare(strict_types=1);

namespace App\Module\Hub\Feature\FileManagement\Controller;

use App\Controller\ApiController;
use App\Module\Hub\Feature\FileManagement\Service\DeleteTagService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/private/file-management')]
class DeleteTagController extends ApiController
{
    public function __construct(
        private readonly DeleteTagService $deleteTagService,
    ) {
    }

    #[Route('/tags/{id}', name: 'api_file_management_delete_tag', methods: ['DELETE'])]
    public function __invoke(int $id): Response
    {
        $this->deleteTagService->deleteTag($id);

        return $this->createSuccessResponse(['message' => 'Tag deleted successfully']);
    }
}
