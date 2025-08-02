<?php

declare(strict_types=1);

namespace App\Controller\EventType;

use App\Controller\ApiController;
use App\Entity\EventType;
use App\Service\EventType\GetEditEventTypeService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/private')]
class GetEditEventTypeController extends ApiController
{
    public function __construct(
        private readonly GetEditEventTypeService $getEditEventTypeService,
    ) {
    }

    #[Route('/event/type/{id}', name: 'api_event_type_edit_details', methods: ['GET'])]
    public function __invoke(EventType $eventType): Response
    {
        $details = $this->getEditEventTypeService->getEditEventTypeDetails($eventType);

        return $this->createSuccessResponse($details);
    }
}
