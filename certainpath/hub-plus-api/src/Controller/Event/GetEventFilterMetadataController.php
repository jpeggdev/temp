<?php

declare(strict_types=1);

namespace App\Controller\Event;

use App\Controller\ApiController;
use App\Service\Event\GetEventFilterMetadataService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/private')]
class GetEventFilterMetadataController extends ApiController
{
    public function __construct(
        private readonly GetEventFilterMetadataService $service,
    ) {
    }

    #[Route(
        '/events/filter-metadata',
        name: 'api_events_filter_metadata',
        methods: ['GET']
    )]
    public function __invoke(): Response
    {
        $responseDto = $this->service->getFilterMetadata();

        return $this->createSuccessResponse($responseDto);
    }
}
