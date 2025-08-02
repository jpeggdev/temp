<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventVenueManagement\Controller;

use App\Controller\ApiController;
use App\Module\EventRegistration\Feature\EventVenueManagement\DTO\Request\CreateUpdateEventVenueDTO;
use App\Module\EventRegistration\Feature\EventVenueManagement\Service\EditEventVenueService;
use App\Module\EventRegistration\Feature\EventVenueManagement\Voter\EventVenueVoter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/private')]
class EditEventVenueController extends ApiController
{
    public function __construct(
        private readonly EditEventVenueService $editEventVenueService,
    ) {
    }

    #[Route(
        '/event-venue/{id}/edit',
        name: 'api_event_venue_edit',
        methods: ['PUT', 'PATCH'],
    )]
    public function __invoke(
        int $id,
        #[MapRequestPayload] CreateUpdateEventVenueDTO $requestDto,
    ): JsonResponse {
        $this->denyAccessUnlessGranted(EventVenueVoter::UPDATE);

        return $this->createSuccessResponse(
            $this->editEventVenueService->editVenue($id, $requestDto)
        );
    }
}
