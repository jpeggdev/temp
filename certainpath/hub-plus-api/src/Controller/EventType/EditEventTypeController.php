<?php

declare(strict_types=1);

namespace App\Controller\EventType;

use App\Controller\ApiController;
use App\DTO\Request\EventType\EditEventTypeDTO;
use App\Entity\EventType;
use App\Service\EventType\EditEventTypeService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/private')]
class EditEventTypeController extends ApiController
{
    public function __construct(
        private readonly EditEventTypeService $editEventTypeService,
    ) {
    }

    #[Route(
        '/event/type/{id}/edit',
        name: 'api_event_type_edit',
        methods: ['PUT', 'PATCH']
    )]
    public function __invoke(
        EventType $eventType,
        #[MapRequestPayload] EditEventTypeDTO $dto,
    ): Response {
        $responseDto = $this->editEventTypeService->editEventType($eventType, $dto);

        return $this->createSuccessResponse($responseDto);
    }
}
