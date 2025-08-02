<?php

declare(strict_types=1);

namespace App\Module\Hub\Feature\FileManagement\Controller;

use App\Controller\ApiController;
use App\Module\Hub\Feature\FileManagement\Service\GetFileManagerMetaDataService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/private/file-management')]
class GetFileManagerMetaDataController extends ApiController
{
    public function __construct(
        private readonly GetFileManagerMetaDataService $getFileManagerMetaDataService,
    ) {
    }

    #[Route('/metadata', name: 'api_file_management_get_metadata', methods: ['GET'])]
    public function __invoke(): Response
    {
        $metadata = $this->getFileManagerMetaDataService->getMetaData();

        return $this->createSuccessResponse($metadata);
    }
}
