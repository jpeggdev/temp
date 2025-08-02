<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventVoucherManagement\Controller;

use App\Controller\ApiController;
use App\Module\EventRegistration\Feature\EventVoucherManagement\DTO\Request\ValidateEventVoucherNameRequestDTO;
use App\Module\EventRegistration\Feature\EventVoucherManagement\Service\ValidateEventVoucherNameService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/private')]
class ValidateEventVoucherNameController extends ApiController
{
    public function __construct(
        private readonly ValidateEventVoucherNameService $validateEventVoucherNameService,
    ) {
    }

    #[Route(
        '/event-voucher/validate-name',
        name: 'api_event_voucher_name_validate',
        methods: ['POST']
    )]
    public function __invoke(
        #[MapRequestPayload] ValidateEventVoucherNameRequestDTO $requestDto,
    ): JsonResponse {
        return $this->createSuccessResponse(
            $this->validateEventVoucherNameService->checkNameExists(
                $requestDto->name,
            )
        );
    }
}
