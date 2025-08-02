<?php

declare(strict_types=1);

namespace App\Controller\EventTag;

use App\Controller\ApiController;
use App\DTO\Request\EventTag\CreateUpdateEventTagDTO;
use App\Service\EventTag\CreateEventTagService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/private')]
class CreateEventTagController extends ApiController
{
    public function __construct(
        private readonly CreateEventTagService $createEventTagService,
    ) {
    }

    #[Route('/event/tag/create', name: 'api_event_tag_create', methods: ['POST'])]
    public function __invoke(
        #[MapRequestPayload] CreateUpdateEventTagDTO $dto,
    ): Response {
        $tagResponse = $this->createEventTagService->createTag($dto);

        return $this->createSuccessResponse($tagResponse);
    }
}
