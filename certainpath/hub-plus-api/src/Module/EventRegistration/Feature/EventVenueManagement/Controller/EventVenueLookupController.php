<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventVenueManagement\Controller;

use App\Controller\ApiController;
use App\Module\EventRegistration\Feature\EventVenueManagement\DTO\Query\EventVenueLookupQueryDTO;
use App\Module\EventRegistration\Feature\EventVenueManagement\Service\EventVenueLookupService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/private')]
class EventVenueLookupController extends ApiController
{
    public function __construct(
        private readonly EventVenueLookupService $venueLookupService,
    ) {
    }

    #[Route('/event-venues/lookup', name: 'api_event_venues_lookup', methods: ['GET'])]
    public function __invoke(
        #[MapQueryString] EventVenueLookupQueryDTO $queryDto,
        Request $request,
    ): Response {
        $result = $this->venueLookupService->lookupVenues($queryDto);

        return $this->createSuccessResponse(
            $result['venues'],
            $result['totalCount']
        );
    }
}
