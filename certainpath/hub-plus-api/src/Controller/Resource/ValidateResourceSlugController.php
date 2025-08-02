<?php

declare(strict_types=1);

namespace App\Controller\Resource;

use App\Controller\ApiController;
use App\DTO\Request\Resource\ValidateResourceSlugRequestDTO;
use App\Service\Resource\ValidateResourceSlugService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

class ValidateResourceSlugController extends ApiController
{
    public function __construct(
        private readonly ValidateResourceSlugService $validateResourceSlugService,
    ) {
    }

    #[Route('/api/private/resource/validate-slug', name: 'api_resource_slug_validate', methods: ['POST'])]
    public function __invoke(
        #[MapRequestPayload] ValidateResourceSlugRequestDTO $requestDto,
    ): JsonResponse {
        return $this->createSuccessResponse(
            $this->validateResourceSlugService->slugExists($requestDto->slug, $requestDto->resourceUuid)
        );
    }
}
