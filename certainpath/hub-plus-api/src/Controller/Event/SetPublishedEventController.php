<?php

declare(strict_types=1);

namespace App\Controller\Event;

use App\Controller\ApiController;
use App\DTO\Request\Event\SetPublishedEventRequestDTO;
use App\Entity\Event;
use App\Service\Event\SetPublishedEventService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/private/events')]
class SetPublishedEventController extends ApiController
{
    public function __construct(
        private readonly SetPublishedEventService $setPublishedEventService,
    ) {
    }

    #[Route(
        '/{uuid}/published',
        name: 'api_events_set_published',
        methods: ['PATCH']
    )]
    public function __invoke(
        Event $event,
        #[MapRequestPayload] SetPublishedEventRequestDTO $dto,
    ): Response {
        $responseDTO = $this->setPublishedEventService->setPublished($event, $dto->isPublished);

        return $this->createSuccessResponse($responseDTO);
    }
}
