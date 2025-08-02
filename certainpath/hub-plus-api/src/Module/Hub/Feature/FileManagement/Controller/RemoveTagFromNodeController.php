<?php

declare(strict_types=1);

namespace App\Module\Hub\Feature\FileManagement\Controller;

use App\Controller\ApiController;
use App\Module\Hub\Feature\FileManagement\DTO\Request\RemoveTagFromNodeRequestDTO;
use App\Module\Hub\Feature\FileManagement\Service\RemoveTagFromNodeService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/private/file-management')]
class RemoveTagFromNodeController extends ApiController
{
    public function __construct(
        private readonly RemoveTagFromNodeService $removeTagFromNodeService,
    ) {
    }

    #[Route('/tags/remove-from-node', name: 'api_file_management_remove_tag_from_node', methods: ['POST'])]
    public function __invoke(
        #[MapRequestPayload] RemoveTagFromNodeRequestDTO $requestDTO,
    ): Response {
        $this->removeTagFromNodeService->removeTagFromNode($requestDTO);

        return $this->createSuccessResponse(['message' => 'Tag removed from node successfully']);
    }
}
