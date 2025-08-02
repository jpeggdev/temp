<?php

declare(strict_types=1);

namespace App\Controller\Event;

use App\Controller\ApiController;
use App\DTO\Request\Event\ValidateEventCodeRequestDTO;
use App\Service\Event\ValidateEventCodeService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

class ValidateEventCodeController extends ApiController
{
    public function __construct(
        private readonly ValidateEventCodeService $validateEventCodeService,
    ) {
    }

    #[Route('/api/private/event/validate-code', name: 'api_event_code_validate', methods: ['POST'])]
    public function __invoke(
        #[MapRequestPayload] ValidateEventCodeRequestDTO $requestDto,
    ): JsonResponse {
        return $this->createSuccessResponse(
            $this->validateEventCodeService->codeExists(
                $requestDto->eventCode,
                $requestDto->eventUuid
            )
        );
    }
}
