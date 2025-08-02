<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventWaitlist\Controller;

use App\Controller\ApiController;
use App\DTO\LoggedInUserDTO;
use App\Entity\EventSession;
use App\Module\EventRegistration\Feature\EventWaitlist\DTO\Request\RemoveWaitlistItemRequestDTO;
use App\Module\EventRegistration\Feature\EventWaitlist\Service\RemoveWaitlistItemService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/api/private')]
class RemoveWaitlistItemController extends ApiController
{
    public function __construct(
        private readonly RemoveWaitlistItemService $removeService,
    ) {
    }

    #[Route(
        '/event-sessions/{uuid}/waitlist/remove',
        name: 'api_event_session_waitlist_remove',
        requirements: ['uuid' => '[0-9A-Fa-f]{8}\-[0-9A-Fa-f]{4}\-[0-9A-Fa-f]{4}\-[0-9A-Fa-f]{4}\-[0-9A-Fa-f]{12}'],
        methods: ['POST']
    )]
    public function __invoke(
        LoggedInUserDTO $loggedInUserDTO,
        EventSession $eventSession,
        #[MapRequestPayload] RemoveWaitlistItemRequestDTO $requestDTO,
    ): Response {
        $responseDTO = $this->removeService->removeItem($eventSession, $requestDTO);

        return $this->createSuccessResponse($responseDTO);
    }
}
