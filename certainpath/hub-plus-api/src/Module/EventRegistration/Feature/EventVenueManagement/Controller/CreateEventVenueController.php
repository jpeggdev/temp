<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventVenueManagement\Controller;

use App\Controller\ApiController;
use App\Module\EventRegistration\Feature\EventVenueManagement\DTO\Request\CreateUpdateEventVenueDTO;
use App\Module\EventRegistration\Feature\EventVenueManagement\Service\CreateEventVenueService;
use App\Module\EventRegistration\Feature\EventVenueManagement\Voter\EventVenueVoter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/private')]
class CreateEventVenueController extends ApiController
{
    public function __construct(
        private readonly CreateEventVenueService $createEventVenueService,
    ) {
    }

    #[Route(
        '/event-venue/create',
        name: 'api_event_venue_create',
        methods: ['POST']
    )]
    public function __invoke(
        #[MapRequestPayload] CreateUpdateEventVenueDTO $requestDto,
    ): JsonResponse {
        $this->denyAccessUnlessGranted(EventVenueVoter::READ);

        return $this->createSuccessResponse(
            $this->createEventVenueService->createVenue($requestDto)
        );
    }
}
