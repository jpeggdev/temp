<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventVoucherManagement\Controller;

use App\Controller\ApiController;
use App\Module\EventRegistration\Feature\EventVoucherManagement\Service\GetEventVoucherService;
use App\Module\EventRegistration\Feature\EventVoucherManagement\Voter\EventVoucherVoter;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/private')]
class GetEventVoucherController extends ApiController
{
    public function __construct(
        private readonly GetEventVoucherService $getEventVoucherService,
    ) {
    }

    #[Route('/event-voucher/{id}', name: 'api_event_voucher_get', methods: ['GET'])]
    public function __invoke(int $id): Response
    {
        $this->denyAccessUnlessGranted(EventVoucherVoter::READ);

        $eventVouchers = $this->getEventVoucherService->getVoucher($id);

        return $this->createSuccessResponse($eventVouchers);
    }
}
