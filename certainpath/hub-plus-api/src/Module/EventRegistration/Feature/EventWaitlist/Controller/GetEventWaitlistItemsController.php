<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventWaitlist\Controller;

use App\Controller\ApiController;
use App\DTO\LoggedInUserDTO;
use App\Entity\EventSession;
use App\Module\EventRegistration\Feature\EventWaitlist\DTO\Request\EventWaitlistItemsQueryDTO;
use App\Module\EventRegistration\Feature\EventWaitlist\Service\GetEventWaitlistItemsService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/private')]
class GetEventWaitlistItemsController extends ApiController
{
    public function __construct(
        private readonly GetEventWaitlistItemsService $eventWaitlistItemsService,
    ) {
    }

    #[Route(
        '/event-sessions/{uuid}/waitlist/items',
        name: 'api_event_session_waitlist_items',
        requirements: ['uuid' => '[0-9A-Fa-f]{8}\-[0-9A-Fa-f]{4}\-[0-9A-Fa-f]{4}\-[0-9A-Fa-f]{4}\-[0-9A-Fa-f]{12}'],
        methods: ['GET']
    )]
    public function __invoke(
        LoggedInUserDTO $loggedInUserDTO,
        EventSession $eventSession,
        #[MapQueryString] EventWaitlistItemsQueryDTO $queryDto = new EventWaitlistItemsQueryDTO(),
    ): JsonResponse {
        $result = $this->eventWaitlistItemsService->getEventWaitlistItems($eventSession, $queryDto);

        return $this->createSuccessResponse(
            $result['items'],
            $result['totalCount']
        );
    }
}
