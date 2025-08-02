<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventDiscountManagement\Controller;

use App\Controller\ApiController;
use App\Module\EventRegistration\Feature\EventDiscountManagement\DTO\Request\ValidateEventDiscountCodeRequestDTO;
use App\Module\EventRegistration\Feature\EventDiscountManagement\Service\ValidateEventDiscountCodeService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/api/private')]
class ValidateEventDiscountCodeController extends ApiController
{
    public function __construct(
        private readonly ValidateEventDiscountCodeService $validateEventDiscountCodeService,
    ) {
    }

    #[Route(
        '/event-discount/validate-code',
        name: 'api_event_discount_code_validate',
        methods: ['POST']
    )]
    public function __invoke(
        #[MapRequestPayload] ValidateEventDiscountCodeRequestDTO $requestDto,
    ): JsonResponse {
        return $this->createSuccessResponse(
            $this->validateEventDiscountCodeService->checkCodeExists(
                $requestDto->code,
            )
        );
    }
}
