<?php

declare(strict_types=1);

namespace App\Controller\Resource;

use App\Controller\ApiController;
use App\DTO\LoggedInUserDTO;
use App\DTO\Request\Resource\CreateUpdateResourceDTO;
use App\Entity\Resource;
use App\Security\Voter\ResourceSecurityVoter;
use App\Service\Resource\UpdateResourceService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/private')]
class UpdateResourceController extends ApiController
{
    public function __construct(
        private readonly UpdateResourceService $updateResourceService,
    ) {
    }

    /**
     * @throws \Exception
     */
    #[Route('/resource/{id}/update', name: 'api_resource_update', methods: ['PUT', 'PATCH'])]
    public function __invoke(
        Resource $resource,
        #[MapRequestPayload] CreateUpdateResourceDTO $updateResourceDTO,
        LoggedInUserDTO $loggedInUserDTO,
    ): Response {
        $this->denyAccessUnlessGranted(ResourceSecurityVoter::MANAGE);
        $updatedResource = $this->updateResourceService->updateResource(
            $resource,
            $updateResourceDTO
        );

        return $this->createSuccessResponse($updatedResource);
    }
}
