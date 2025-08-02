<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventWaitlist\Controller;

use App\Controller\ApiController;
use App\Entity\EventSession;
use App\Module\EventRegistration\Feature\EventWaitlist\Service\GetWaitlistDetailsService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/private')]
class GetWaitlistDetailsController extends ApiController
{
    public function __construct(
        private readonly GetWaitlistDetailsService $service,
    ) {
    }

    #[Route(
        '/event-sessions/{uuid}/waitlist',
        name: 'api_event_session_waitlist_details',
        requirements: ['uuid' => '[0-9A-Fa-f]{8}\-[0-9A-Fa-f]{4}\-[0-9A-Fa-f]{4}\-[0-9A-Fa-f]{4}\-[0-9A-Fa-f]{12}'],
        methods: ['GET']
    )]
    public function __invoke(EventSession $eventSession): Response
    {
        $responseDTO = $this->service->getWaitlistDetails($eventSession);

        return $this->createSuccessResponse($responseDTO);
    }
}
