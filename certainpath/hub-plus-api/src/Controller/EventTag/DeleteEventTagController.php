<?php

declare(strict_types=1);

namespace App\Controller\EventTag;

use App\Controller\ApiController;
use App\Entity\EventTag;
use App\Service\EventTag\DeleteEventTagService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/private')]
class DeleteEventTagController extends ApiController
{
    public function __construct(
        private readonly DeleteEventTagService $deleteEventTagService,
    ) {
    }

    #[Route(
        '/event/tag/{id}/delete',
        name: 'api_event_tag_delete',
        methods: ['DELETE']
    )]
    public function __invoke(EventTag $eventTag): Response
    {
        $this->deleteEventTagService->deleteTag($eventTag);

        return $this->createSuccessResponse([
            'message' => 'Event tag deleted successfully.',
        ]);
    }
}
