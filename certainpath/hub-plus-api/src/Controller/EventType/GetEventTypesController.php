<?php

declare(strict_types=1);

namespace App\Controller\EventType;

use App\Controller\ApiController;
use App\DTO\LoggedInUserDTO;
use App\DTO\Request\EventType\GetEventTypesRequestDTO;
use App\Service\EventType\GetEventTypesService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/private')]
class GetEventTypesController extends ApiController
{
    public function __construct(
        private readonly GetEventTypesService $getEventTypesService,
    ) {
    }

    #[Route('/event-types', name: 'api_event_types_get', methods: ['GET'])]
    public function __invoke(
        #[MapQueryString] GetEventTypesRequestDTO $requestDto,
        LoggedInUserDTO $loggedInUserDTO,
        Request $request,
    ): Response {
        $data = $this->getEventTypesService->getEventTypes($requestDto);

        return $this->createSuccessResponse(
            $data,
            $data['totalCount']
        );
    }
}
