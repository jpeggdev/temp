<?php

declare(strict_types=1);

namespace App\Controller\ResourceTag;

use App\Controller\ApiController;
use App\DTO\Request\ResourceTag\CreateUpdateResourceTagDTO;
use App\Entity\ResourceTag;
use App\Security\Voter\ResourceTagSecurityVoter;
use App\Service\ResourceTag\EditResourceTagService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/private')]
class EditResourceTagController extends ApiController
{
    public function __construct(
        private readonly EditResourceTagService $editResourceTagService,
    ) {
    }

    #[Route('/resource/tag/{id}/edit', name: 'api_resource_tag_edit', methods: ['PUT', 'PATCH'])]
    public function __invoke(
        ResourceTag $resourceTag,
        #[MapRequestPayload] CreateUpdateResourceTagDTO $dto,
    ): Response {
        $this->denyAccessUnlessGranted(ResourceTagSecurityVoter::MANAGE, $resourceTag);
        $responseDTO = $this->editResourceTagService->editTag($resourceTag, $dto);

        return $this->createSuccessResponse($responseDTO);
    }
}
