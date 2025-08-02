<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventDiscountManagement\Controller;

use App\Controller\ApiController;
use App\Module\EventRegistration\Feature\EventDiscountManagement\Service\DeleteEventDiscountService;
use App\Module\EventRegistration\Feature\EventDiscountManagement\Voter\EventDiscountVoter;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/private')]
class DeleteEventDiscountController extends ApiController
{
    public function __construct(
        private readonly DeleteEventDiscountService $deleteEventDiscountService,
    ) {
    }

    #[Route(
        '/event-discount/{id}/delete',
        name: 'api_event_discount_delete',
        methods: ['DELETE']
    )]
    public function __invoke(int $id): Response
    {
        $this->denyAccessUnlessGranted(EventDiscountVoter::DELETE);

        $deletedEventDiscount = $this->deleteEventDiscountService->deleteEventDiscount($id);

        return $this->createSuccessResponse([
            'message' => sprintf('Event Discount %d has been deleted.', $deletedEventDiscount['code']),
        ]);
    }
}
