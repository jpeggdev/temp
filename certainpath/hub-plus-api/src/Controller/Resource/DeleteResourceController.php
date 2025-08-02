<?php

declare(strict_types=1);

namespace App\Controller\Resource;

use App\Controller\ApiController;
use App\DTO\LoggedInUserDTO;
use App\Entity\Resource;
use App\Security\Voter\ResourceSecurityVoter;
use App\Service\Resource\DeleteResourceService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/private')]
class DeleteResourceController extends ApiController
{
    public function __construct(
        private readonly DeleteResourceService $deleteResourceService,
    ) {
    }

    #[Route('/resource/{uuid}', name: 'api_resource_delete', methods: ['DELETE'])]
    public function __invoke(Resource $resource, LoggedInUserDTO $loggedInUserDTO): Response
    {
        $this->denyAccessUnlessGranted(ResourceSecurityVoter::MANAGE, $resource);

        $this->deleteResourceService->deleteResource($resource);

        return $this->createSuccessResponse([
            'message' => 'Resource deleted successfully',
        ]);
    }
}
