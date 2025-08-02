<?php

declare(strict_types=1);

namespace App\Controller\Resource;

use App\Controller\ApiController;
use App\DTO\Request\Resource\SetPublishedResourceRequestDTO;
use App\Entity\Resource;
use App\Service\Resource\SetPublishedResourceService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/private/resources')]
class SetPublishedResourceController extends ApiController
{
    public function __construct(
        private readonly SetPublishedResourceService $setPublishedResourceService,
    ) {
    }

    #[Route(
        '/{uuid}/published',
        name: 'api_resources_set_published',
        methods: ['PATCH']
    )]
    public function __invoke(
        Resource $resource,
        #[MapRequestPayload] SetPublishedResourceRequestDTO $dto,
    ): Response {
        $responseDTO = $this->setPublishedResourceService->setPublished($resource, $dto->isPublished);

        return $this->createSuccessResponse($responseDTO);
    }
}
