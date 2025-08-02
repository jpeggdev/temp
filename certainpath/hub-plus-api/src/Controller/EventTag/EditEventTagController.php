<?php

// src/Controller/EventTag/EditEventTagController.php

declare(strict_types=1);

namespace App\Controller\EventTag;

use App\Controller\ApiController;
use App\DTO\Request\EventTag\CreateUpdateEventTagDTO;
use App\Entity\EventTag;
use App\Service\EventTag\EditEventTagService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/private')]
class EditEventTagController extends ApiController
{
    public function __construct(
        private readonly EditEventTagService $editEventTagService,
    ) {
    }

    #[Route(
        '/event/tag/{id}/edit',
        name: 'api_event_tag_edit',
        methods: ['PUT', 'PATCH']
    )]
    public function __invoke(
        EventTag $eventTag,
        #[MapRequestPayload] CreateUpdateEventTagDTO $dto,
    ): Response {
        $responseDTO = $this->editEventTagService->editTag($eventTag, $dto);

        return $this->createSuccessResponse($responseDTO);
    }
}
