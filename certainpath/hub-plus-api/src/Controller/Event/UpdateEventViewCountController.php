<?php

declare(strict_types=1);

namespace App\Controller\Event;

use App\Controller\ApiController;
use App\Entity\Event;
use App\Service\Event\UpdateEventViewCountService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/private')]
class UpdateEventViewCountController extends ApiController
{
    public function __construct(
        private readonly UpdateEventViewCountService $viewCountService,
    ) {
    }

    #[Route(
        '/events/{uuid}/views',
        name: 'api_event_increment_views',
        requirements: ['uuid' => '[0-9A-Fa-f]{8}\-[0-9A-Fa-f]{4}\-[0-9A-Fa-f]{4}\-[0-9A-Fa-f]{4}\-[0-9A-Fa-f]{12}'],
        methods: ['POST']
    )]
    public function __invoke(Event $event, Request $request): Response
    {
        $this->viewCountService->incrementViewCount($event);

        return $this->createSuccessResponse([
            'message' => 'Event view count incremented successfully',
        ]);
    }
}
