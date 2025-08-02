<?php

declare(strict_types=1);

namespace App\Module\Hub\Feature\FileManagement\Controller;

use App\Controller\ApiController;
use App\Module\Hub\Feature\FileManagement\DTO\Request\CreateAndAssignTagRequestDTO;
use App\Module\Hub\Feature\FileManagement\Service\CreateAndAssignTagService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/private/file-management')]
class CreateAndAssignTagController extends ApiController
{
    public function __construct(
        private readonly CreateAndAssignTagService $createAndAssignTagService,
    ) {
    }

    #[Route('/tags/create-and-assign', name: 'api_file_management_create_and_assign_tag', methods: ['POST'])]
    public function __invoke(
        #[MapRequestPayload] CreateAndAssignTagRequestDTO $requestDTO,
    ): Response {
        $response = $this->createAndAssignTagService->createAndAssignTag($requestDTO);

        return $this->createSuccessResponse($response);
    }
}
