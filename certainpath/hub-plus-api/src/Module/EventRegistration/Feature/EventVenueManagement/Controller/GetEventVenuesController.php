<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventVenueManagement\Controller;

use App\Controller\ApiController;
use App\DTO\Query\PaginationDTO;
use App\Module\EventRegistration\Feature\EventVenueManagement\DTO\Query\GetEventVenuesDTO;
use App\Module\EventRegistration\Feature\EventVenueManagement\Service\GetEventVenueService;
use App\Module\EventRegistration\Feature\EventVenueManagement\Voter\EventVenueVoter;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/private')]
class GetEventVenuesController extends ApiController
{
    public function __construct(
        private readonly GetEventVenueService $getEventVoucherService,
    ) {
    }

    #[Route('/event-venues', name: 'api_event_venues_get', methods: ['GET'])]
    public function __invoke(
        #[MapQueryString] GetEventVenuesDTO $queryDto,
        #[MapQueryString] PaginationDTO $paginationDTO,
    ): Response {
        $this->denyAccessUnlessGranted(EventVenueVoter::READ);
        $eventVenuesData = $this->getEventVoucherService->getVenues($queryDto, $paginationDTO);

        return $this->createSuccessResponse(
            $eventVenuesData['eventVenues'],
            $eventVenuesData['totalCount']
        );
    }
}
