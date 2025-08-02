<?php

declare(strict_types=1);

namespace App\Controller\ResourceTag;

use App\Controller\ApiController;
use App\Entity\ResourceTag;
use App\Security\Voter\ResourceTagSecurityVoter;
use App\Service\ResourceTag\DeleteResourceTagService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/private')]
class DeleteResourceTagController extends ApiController
{
    public function __construct(
        private readonly DeleteResourceTagService $deleteResourceTagService,
    ) {
    }

    #[Route('/resource/tag/{id}/delete', name: 'api_resource_tag_delete', methods: ['DELETE'])]
    public function __invoke(ResourceTag $resourceTag): Response
    {
        $this->denyAccessUnlessGranted(ResourceTagSecurityVoter::MANAGE, $resourceTag);
        $this->deleteResourceTagService->deleteTag($resourceTag);

        return $this->createSuccessResponse(['message' => 'Tag deleted successfully.']);
    }
}
