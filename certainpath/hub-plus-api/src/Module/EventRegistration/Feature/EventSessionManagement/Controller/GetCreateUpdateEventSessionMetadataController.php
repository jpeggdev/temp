<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventSessionManagement\Controller;

use App\Controller\ApiController;
use App\Module\EventRegistration\Feature\EventSessionManagement\Service\GetCreateUpdateEventSessionMetadataService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/private')]
class GetCreateUpdateEventSessionMetadataController extends ApiController
{
    public function __construct(
        private readonly GetCreateUpdateEventSessionMetadataService $service,
    ) {
    }

    #[Route(
        '/event-create-update-session-metadata',
        name: 'api_events_create_update_session_metadata',
        methods: ['GET']
    )]
    public function __invoke(): Response
    {
        $responseDto = $this->service->getCreateUpdateEventSessionMetadata();

        return $this->createSuccessResponse($responseDto);
    }
}
