<?php

declare(strict_types=1);

namespace App\Module\Hub\Feature\FileManagement\Controller;

use App\Controller\ApiController;
use App\Module\Hub\Feature\FileManagement\Service\ListTagsService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/private/file-management')]
class ListTagsController extends ApiController
{
    public function __construct(
        private readonly ListTagsService $listTagsService,
    ) {
    }

    #[Route('/tags', name: 'api_file_management_list_tags', methods: ['GET'])]
    public function __invoke(): Response
    {
        $response = $this->listTagsService->listTags();

        return $this->createSuccessResponse($response);
    }
}
