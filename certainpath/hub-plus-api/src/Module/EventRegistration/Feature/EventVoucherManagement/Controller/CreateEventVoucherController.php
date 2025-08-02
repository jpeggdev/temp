<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventVoucherManagement\Controller;

use App\Controller\ApiController;
use App\Module\EventRegistration\Feature\EventVoucherManagement\DTO\Request\CreateEventVoucherDTO;
use App\Module\EventRegistration\Feature\EventVoucherManagement\Service\CreateEventVoucherService;
use App\Module\EventRegistration\Feature\EventVoucherManagement\Voter\EventVoucherVoter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/private')]
class CreateEventVoucherController extends ApiController
{
    public function __construct(
        private readonly CreateEventVoucherService $createEventVoucherService,
    ) {
    }

    #[Route(
        '/event-voucher/create',
        name: 'api_event_voucher_create',
        methods: ['POST']
    )]
    public function __invoke(
        #[MapRequestPayload] CreateEventVoucherDTO $requestDto,
    ): JsonResponse {
        $this->denyAccessUnlessGranted(EventVoucherVoter::CREATE);

        return $this->createSuccessResponse(
            $this->createEventVoucherService->createVoucher($requestDto)
        );
    }
}
