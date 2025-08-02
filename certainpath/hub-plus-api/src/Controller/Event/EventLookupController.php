<?php

declare(strict_types=1);

namespace App\Controller\Event;

use App\Controller\ApiController;
use App\DTO\Request\Event\EventLookupRequestDTO;
use App\Service\Event\EventLookupService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/private')]
class EventLookupController extends ApiController
{
    public function __construct(
        private readonly EventLookupService $eventLookupService,
    ) {
    }

    #[Route('/events/lookup', name: 'api_events_lookup', methods: ['GET'])]
    public function __invoke(
        #[MapQueryString] EventLookupRequestDTO $queryDto,
        Request $request,
    ): Response {
        $data = $this->eventLookupService->lookupEvents($queryDto);

        return $this->createSuccessResponse(
            $data['events'],
            $data['totalCount']
        );
    }
}
