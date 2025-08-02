<?php

declare(strict_types=1);

namespace App\Controller\Resource;

use App\Controller\ApiController;
use App\DTO\LoggedInUserDTO;
use App\Entity\Resource;
use App\Service\Resource\GetResourceService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/private')]
class GetResourceController extends ApiController
{
    public function __construct(
        private readonly GetResourceService $getResourceService,
    ) {
    }

    #[Route(
        '/resource/{uuid}',
        name: 'api_resource_get',
        requirements: ['uuid' => '[0-9A-Fa-f]{8}\-[0-9A-Fa-f]{4}\-[0-9A-Fa-f]{4}\-[0-9A-Fa-f]{4}\-[0-9A-Fa-f]{12}'],
        methods: ['GET']
    )]
    public function __invoke(Resource $resource, LoggedInUserDTO $loggedInUserDTO): Response
    {
        $resourceData = $this->getResourceService->getResource($resource, $loggedInUserDTO->getActiveEmployee());

        return $this->createSuccessResponse($resourceData);
    }
}
