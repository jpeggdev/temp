<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventDiscountManagement\Controller;

use App\Controller\ApiController;
use App\Module\EventRegistration\Feature\EventDiscountManagement\DTO\Request\CreateUpdateEventDiscountDTO;
use App\Module\EventRegistration\Feature\EventDiscountManagement\Service\CreateEventDiscountService;
use App\Module\EventRegistration\Feature\EventDiscountManagement\Voter\EventDiscountVoter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/private')]
class CreateEventDiscountController extends ApiController
{
    public function __construct(
        private readonly CreateEventDiscountService $createEventDiscountService,
    ) {
    }

    #[Route(
        '/event-discount/create',
        name: 'api_event_discount_create',
        methods: ['POST']
    )]
    public function __invoke(
        #[MapRequestPayload] CreateUpdateEventDiscountDTO $requestDto,
    ): JsonResponse {
        $this->denyAccessUnlessGranted(EventDiscountVoter::CREATE);

        return $this->createSuccessResponse(
            $this->createEventDiscountService->createDiscount($requestDto)
        );
    }
}
