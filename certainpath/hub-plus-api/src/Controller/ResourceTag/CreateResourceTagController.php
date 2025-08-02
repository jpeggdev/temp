<?php

declare(strict_types=1);

namespace App\Controller\ResourceTag;

use App\Controller\ApiController;
use App\DTO\Request\ResourceTag\CreateUpdateResourceTagDTO;
use App\Security\Voter\ResourceTagSecurityVoter;
use App\Service\ResourceTag\CreateResourceTagService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/private')]
class CreateResourceTagController extends ApiController
{
    public function __construct(
        private readonly CreateResourceTagService $createResourceTagService,
    ) {
    }

    #[Route('/resource/tag/create', name: 'api_resource_tag_create', methods: ['POST'])]
    public function __invoke(
        #[MapRequestPayload] CreateUpdateResourceTagDTO $dto,
    ): Response {
        $this->denyAccessUnlessGranted(ResourceTagSecurityVoter::MANAGE);
        $tagResponse = $this->createResourceTagService->createTag($dto);

        return $this->createSuccessResponse($tagResponse);
    }
}
