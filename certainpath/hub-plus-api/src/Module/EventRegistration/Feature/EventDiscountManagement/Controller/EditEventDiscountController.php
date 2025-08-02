<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventDiscountManagement\Controller;

use App\Controller\ApiController;
use App\Module\EventRegistration\Feature\EventDiscountManagement\DTO\Request\CreateUpdateEventDiscountDTO;
use App\Module\EventRegistration\Feature\EventDiscountManagement\Service\EditEventDiscountService;
use App\Module\EventRegistration\Feature\EventDiscountManagement\Voter\EventDiscountVoter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/private')]
class EditEventDiscountController extends ApiController
{
    public function __construct(
        private readonly EditEventDiscountService $editEventDiscountService,
    ) {
    }

    /**
     * @throws \Exception
     */
    #[Route(
        '/event-discount/{id}/edit',
        name: 'api_event_discount_edit',
        methods: ['PUT', 'PATCH'],
    )]
    public function __invoke(
        int $id,
        #[MapRequestPayload] CreateUpdateEventDiscountDTO $requestDto,
    ): JsonResponse {
        $this->denyAccessUnlessGranted(EventDiscountVoter::UPDATE);

        return $this->createSuccessResponse(
            $this->editEventDiscountService->editDiscount($id, $requestDto)
        );
    }
}
