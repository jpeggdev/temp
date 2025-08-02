<?php

declare(strict_types=1);

namespace App\Controller\Resource;

use App\Controller\ApiController;
use App\DTO\LoggedInUserDTO;
use App\Service\Resource\GetCreateUpdateResourceMetadataService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/private')]
class GetCreateUpdateResourceMetadataController extends ApiController
{
    public function __construct(
        private readonly GetCreateUpdateResourceMetadataService $getCreateUpdateResourceMetadataService,
    ) {
    }

    #[Route(
        '/create-update-resource-metadata',
        name: 'api_resource_get_create_update_metadata',
        methods: ['GET']
    )]
    public function __invoke(LoggedInUserDTO $loggedInUserDTO): Response
    {
        $metadataDto = $this->getCreateUpdateResourceMetadataService->getMetadata();

        return $this->createSuccessResponse($metadataDto);
    }
}
