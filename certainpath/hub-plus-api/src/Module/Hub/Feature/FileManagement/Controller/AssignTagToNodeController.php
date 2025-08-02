<?php

declare(strict_types=1);

namespace App\Module\Hub\Feature\FileManagement\Controller;

use App\Controller\ApiController;
use App\Module\Hub\Feature\FileManagement\DTO\Request\AssignTagToNodeRequestDTO;
use App\Module\Hub\Feature\FileManagement\Service\AssignTagToNodeService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/private/file-management')]
class AssignTagToNodeController extends ApiController
{
    public function __construct(
        private readonly AssignTagToNodeService $assignTagToNodeService,
    ) {
    }

    #[Route('/tags/assign-to-node', name: 'api_file_management_assign_tag_to_node', methods: ['POST'])]
    public function __invoke(
        #[MapRequestPayload] AssignTagToNodeRequestDTO $requestDTO,
    ): Response {
        $response = $this->assignTagToNodeService->assignTagToNode($requestDTO);

        return $this->createSuccessResponse($response);
    }
}
