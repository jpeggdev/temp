<?php

declare(strict_types=1);

namespace App\Controller\Event;

use App\Controller\ApiController;
use App\Service\Event\GetCreateUpdateEventMetadataService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/private')]
class GetCreateUpdateEventMetadataController extends ApiController
{
    public function __construct(
        private readonly GetCreateUpdateEventMetadataService $service,
    ) {
    }

    #[Route(
        '/event-create-update-metadata',
        name: 'api_events_create_update_metadata',
        methods: ['GET']
    )]
    public function __invoke(): Response
    {
        $responseDto = $this->service->getCreateUpdateEventMetadata();

        return $this->createSuccessResponse($responseDto);
    }
}
