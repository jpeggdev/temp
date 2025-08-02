<?php

declare(strict_types=1);

namespace App\Controller\Resource;

use App\Controller\ApiController;
use App\Entity\Resource;
use App\Service\Resource\UpdateResourceViewCountService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/private')]
class UpdateResourceViewCountController extends ApiController
{
    public function __construct(
        private readonly UpdateResourceViewCountService $viewCountService,
    ) {
    }

    #[Route('/resources/{uuid}/views', name: 'api_resource_increment_views', methods: ['POST'])]
    public function __invoke(Resource $resource, Request $request): Response
    {
        $this->viewCountService->incrementViewCount($resource);

        return $this->createSuccessResponse([
            'message' => 'View count incremented successfully',
        ]);
    }
}
