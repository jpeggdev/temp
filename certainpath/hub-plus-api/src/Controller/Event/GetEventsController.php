<?php

declare(strict_types=1);

namespace App\Controller\Event;

use App\Controller\ApiController;
use App\DTO\Request\Event\GetEventsRequestDTO;
use App\Service\Event\GetEventsService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/private')]
class GetEventsController extends ApiController
{
    public function __construct(
        private readonly GetEventsService $getEventsService,
    ) {
    }

    #[Route('/events', name: 'api_events_get', methods: ['GET'])]
    public function __invoke(
        #[MapQueryString] GetEventsRequestDTO $queryDto,
        Request $request,
    ): Response {
        $data = $this->getEventsService->getEvents($queryDto);

        return $this->createSuccessResponse(
            $data['events'],
            $data['totalCount']
        );
    }
}
