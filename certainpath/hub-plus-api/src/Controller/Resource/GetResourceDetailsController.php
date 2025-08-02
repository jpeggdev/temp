<?php

declare(strict_types=1);

namespace App\Controller\Resource;

use App\Controller\ApiController;
use App\DTO\LoggedInUserDTO;
use App\Entity\Resource;
use App\Service\Resource\GetResourceDetailsService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/private')]
class GetResourceDetailsController extends ApiController
{
    public function __construct(
        private readonly GetResourceDetailsService $getResourceDetailsService,
    ) {
    }

    #[Route('/resource-details/{slug}', name: 'api_resource_details_get', methods: ['GET'])]
    public function __invoke(Resource $resource, LoggedInUserDTO $loggedInUserDTO): Response
    {
        $resourceData = $this->getResourceDetailsService
            ->getResourceDetails($resource, $loggedInUserDTO->getActiveEmployee());

        return $this->createSuccessResponse($resourceData);
    }
}
