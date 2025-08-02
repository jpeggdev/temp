<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventVoucherManagement\Controller;

use App\Controller\ApiController;
use App\Module\EventRegistration\Feature\EventVoucherManagement\Service\DeleteEventVoucherService;
use App\Module\EventRegistration\Feature\EventVoucherManagement\Voter\EventVoucherVoter;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/private')]
class DeleteEventVoucherController extends ApiController
{
    public function __construct(
        private readonly DeleteEventVoucherService $deleteEventVoucherService,
    ) {
    }

    #[Route(
        '/event-voucher/{id}/delete',
        name: 'api_event_voucher_delete',
        methods: ['DELETE']
    )]
    public function __invoke(int $id): Response
    {
        $this->denyAccessUnlessGranted(EventVoucherVoter::DELETE);

        $deletedEventDiscount = $this->deleteEventVoucherService->deleteEventVoucher($id);

        return $this->createSuccessResponse([
            'message' => sprintf('Event Discount %d has been deleted.', $deletedEventDiscount['code']),
        ]);
    }
}
