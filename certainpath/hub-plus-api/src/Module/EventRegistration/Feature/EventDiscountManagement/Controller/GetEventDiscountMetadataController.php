<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventDiscountManagement\Controller;

use App\Controller\ApiController;
use App\Module\EventRegistration\Feature\EventDiscountManagement\Service\GetEventDiscountMetadataService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/private')]
class GetEventDiscountMetadataController extends ApiController
{
    public function __construct(
        private readonly GetEventDiscountMetadataService $getEventDiscountMetadataService,
    ) {
    }

    #[Route(
        '/event-discount-metadata',
        name: 'api_event_discount_metadata_get',
        methods: ['GET']
    )]
    public function __invoke(): Response
    {
        $metadata = $this->getEventDiscountMetadataService->getMetadata();

        return $this->createSuccessResponse($metadata);
    }
}
