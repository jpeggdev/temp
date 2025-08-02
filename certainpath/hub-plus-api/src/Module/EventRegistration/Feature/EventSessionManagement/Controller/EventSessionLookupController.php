<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventSessionManagement\Controller;

use App\Controller\ApiController;
use App\Module\EventRegistration\Feature\EventSessionManagement\DTO\Request\EventSessionLookupRequestDTO;
use App\Module\EventRegistration\Feature\EventSessionManagement\Service\EventSessionLookupService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/private')]
class EventSessionLookupController extends ApiController
{
    public function __construct(
        private readonly EventSessionLookupService $eventSessionLookupService,
    ) {
    }

    #[Route('/event-sessions/lookup', name: 'api_event_sessions_lookup', methods: ['GET'])]
    public function __invoke(
        #[MapQueryString] EventSessionLookupRequestDTO $queryDto,
        Request $request,
    ): Response {
        $data = $this->eventSessionLookupService->lookupSessions($queryDto);

        return $this->createSuccessResponse(
            $data['sessions'],
            $data['totalCount']
        );
    }
}
