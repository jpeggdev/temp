<?php

declare(strict_types=1);

namespace App\Module\Hub\Feature\ResourceLibrary\Controller\ResourceLibraryMetadata;

use App\Controller\ApiController;
use App\Module\Hub\Feature\ResourceLibrary\Service\GetResourceLibraryMetadataService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/private')]
class GetResourceLibraryMetadataController extends ApiController
{
    public function __construct(
        private readonly GetResourceLibraryMetadataService $getResourceLibraryMetadataService,
    ) {
    }

    #[Route('/resource-library-metadata', name: 'api_resource_library_metadata_get', methods: ['GET'])]
    public function __invoke(): Response
    {
        $metadata = $this->getResourceLibraryMetadataService->getMetadata();

        return $this->createSuccessResponse(
            $metadata,
        );
    }
}
