<?php

declare(strict_types=1);

namespace App\Controller\Resource;

use App\Controller\ApiController;
use App\DTO\Request\Resource\SetFeaturedResourceRequestDTO;
use App\Entity\Resource;
use App\Service\Resource\SetFeaturedResourceService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/private/resources')]
class SetFeaturedResourceController extends ApiController
{
    public function __construct(
        private readonly SetFeaturedResourceService $setFeaturedResourceService,
    ) {
    }

    #[Route(
        '/{uuid}/featured',
        name: 'api_resources_set_featured',
        methods: ['PATCH']
    )]
    public function __invoke(
        Resource $resource,
        #[MapRequestPayload] SetFeaturedResourceRequestDTO $dto,
    ): Response {
        $responseDTO = $this->setFeaturedResourceService->setFeatured($resource, $dto->isFeatured);

        return $this->createSuccessResponse($responseDTO);
    }
}
