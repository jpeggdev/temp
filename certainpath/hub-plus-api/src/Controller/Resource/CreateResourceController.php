<?php

declare(strict_types=1);

namespace App\Controller\Resource;

use App\Controller\ApiController;
use App\DTO\Request\Resource\CreateUpdateResourceDTO;
use App\Security\Voter\ResourceSecurityVoter;
use App\Service\Resource\CreateResourceService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/private')]
class CreateResourceController extends ApiController
{
    public function __construct(
        private readonly CreateResourceService $createResourceService,
    ) {
    }

    #[Route('/resource/create', name: 'api_resource_create', methods: ['POST'])]
    public function __invoke(
        #[MapRequestPayload] CreateUpdateResourceDTO $createResourceDTO,
    ): Response {
        $this->denyAccessUnlessGranted(ResourceSecurityVoter::MANAGE);
        $resourceResponse = $this->createResourceService->createResource(
            $createResourceDTO
        );

        return $this->createSuccessResponse($resourceResponse);
    }
}
