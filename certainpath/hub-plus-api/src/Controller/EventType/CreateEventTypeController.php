<?php

declare(strict_types=1);

namespace App\Controller\EventType;

use App\Controller\ApiController;
use App\DTO\Request\EventType\CreateEventTypeDTO;
use App\Service\EventType\CreateEventTypeService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/private')]
class CreateEventTypeController extends ApiController
{
    public function __construct(
        private readonly CreateEventTypeService $createEventTypeService,
    ) {
    }

    #[Route(
        '/event/type/create',
        name: 'api_event_type_create',
        methods: ['POST']
    )]
    public function __invoke(
        #[MapRequestPayload] CreateEventTypeDTO $dto,
    ): Response {
        $responseDto = $this->createEventTypeService->createEventType($dto);

        return $this->createSuccessResponse($responseDto);
    }
}
