<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventVoucherManagement\Controller;

use App\Controller\ApiController;
use App\Module\EventRegistration\Feature\EventVoucherManagement\DTO\Request\UpdateEventVoucherDTO;
use App\Module\EventRegistration\Feature\EventVoucherManagement\Service\UpdateEventVoucherService;
use App\Module\EventRegistration\Feature\EventVoucherManagement\Voter\EventVoucherVoter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/private')]
class UpdateEventVenueController extends ApiController
{
    public function __construct(
        private readonly UpdateEventVoucherService $updateEventVoucherService,
    ) {
    }

    #[Route(
        '/event-voucher/{id}/edit',
        name: 'api_event_voucher_edit',
        methods: ['PUT', 'PATCH'],
    )]
    public function __invoke(
        int $id,
        #[MapRequestPayload] UpdateEventVoucherDTO $requestDto,
    ): JsonResponse {
        $this->denyAccessUnlessGranted(EventVoucherVoter::UPDATE);

        return $this->createSuccessResponse(
            $this->updateEventVoucherService->updateVoucher($id, $requestDto)
        );
    }
}
