<?php

declare(strict_types=1);

namespace App\Controller\Event;

use App\Controller\ApiController;
use App\DTO\LoggedInUserDTO;
use App\Entity\Event;
use App\Service\Event\GetEventDetailsService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/private')]
class GetEventDetailsController extends ApiController
{
    public function __construct(
        private readonly GetEventDetailsService $getEventDetailsService,
    ) {
    }

    #[Route(
        '/event-details/{uuid}',
        name: 'api_event_details_get',
        requirements: ['uuid' => '[0-9A-Fa-f]{8}\-[0-9A-Fa-f]{4}\-[0-9A-Fa-f]{4}\-[0-9A-Fa-f]{4}\-[0-9A-Fa-f]{12}'],
        methods: ['GET'],
    )]
    public function __invoke(Event $event, LoggedInUserDTO $loggedInUserDTO): Response
    {
        $eventDetails = $this->getEventDetailsService
            ->getEventDetails($event, $loggedInUserDTO);

        return $this->createSuccessResponse($eventDetails);
    }
}
