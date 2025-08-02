<?php

declare(strict_types=1);

namespace App\Module\Hub\Feature\FileManagement\Controller;

use App\Controller\ApiController;
use App\Module\Hub\Feature\FileManagement\Service\GetFileSystemNodeDetailsService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/private/file-management')]
class GetFileSystemNodeDetailsController extends ApiController
{
    public function __construct(
        private readonly GetFileSystemNodeDetailsService $getFileSystemNodeDetailsService,
    ) {
    }

    #[Route('/nodes/{uuid}', name: 'api_file_management_get_node_details', methods: ['GET'])]
    public function __invoke(string $uuid): Response
    {
        $result = $this->getFileSystemNodeDetailsService->getNodeDetails($uuid);

        return $this->createSuccessResponse($result['data']);
    }
}
