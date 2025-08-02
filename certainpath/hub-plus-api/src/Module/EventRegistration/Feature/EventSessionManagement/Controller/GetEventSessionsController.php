<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventSessionManagement\Controller;

use App\Controller\ApiController;
use App\Module\EventRegistration\Feature\EventSessionManagement\DTO\Request\GetEventSessionsRequestDTO;
use App\Module\EventRegistration\Feature\EventSessionManagement\Service\GetEventSessionsService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/private')]
class GetEventSessionsController extends ApiController
{
    public function __construct(private readonly GetEventSessionsService $service)
    {
    }

    #[Route('/event-sessions', name: 'api_event_sessions_get', methods: ['GET'])]
    public function __invoke(
        #[MapQueryString] GetEventSessionsRequestDTO $dto,
        Request $request,
    ): Response {
        $data = $this->service->getSessions($dto);

        return $this->createSuccessResponse(
            $data['data'],
            $data['totalCount']
        );
    }
}
