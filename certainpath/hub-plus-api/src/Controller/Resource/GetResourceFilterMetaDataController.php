<?php

declare(strict_types=1);

namespace App\Controller\Resource;

use App\Controller\ApiController;
use App\Service\Resource\GetResourceFilterMetaDataService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/private')]
class GetResourceFilterMetaDataController extends ApiController
{
    public function __construct(
        private readonly GetResourceFilterMetaDataService $service,
    ) {
    }

    #[Route(
        '/resources/filter-metadata',
        name: 'api_resources_filter_metadata',
        methods: ['GET']
    )]
    public function __invoke(): Response
    {
        $responseDto = $this->service->getFilterMetadata();

        return $this->createSuccessResponse($responseDto);
    }
}
