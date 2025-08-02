<?php

declare(strict_types=1);

namespace App\Controller\ResourceTag;

use App\Controller\ApiController;
use App\Entity\ResourceTag;
use App\Security\Voter\ResourceTagSecurityVoter;
use App\Service\ResourceTag\GetEditResourceTagService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/private')]
class GetEditResourceTagController extends ApiController
{
    public function __construct(
        private readonly GetEditResourceTagService $getEditResourceTagService,
    ) {
    }

    #[Route('/resource/tag/{id}', name: 'api_resource_tag_edit_details', methods: ['GET'])]
    public function __invoke(ResourceTag $resourceTag): Response
    {
        $this->denyAccessUnlessGranted(ResourceTagSecurityVoter::MANAGE, $resourceTag);
        $tagDetails = $this->getEditResourceTagService->getEditResourceTagDetails($resourceTag);

        return $this->createSuccessResponse($tagDetails);
    }
}
