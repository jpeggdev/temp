<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventDiscountManagement\Controller;

use App\Controller\ApiController;
use App\Module\EventRegistration\Feature\EventDiscountManagement\Service\GetEventDiscountsService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/private')]
class GetEventDiscountController extends ApiController
{
    public function __construct(
        private readonly GetEventDiscountsService $getEventDiscountsService,
    ) {
    }

    #[Route(
        '/event-discount/{id}',
        name: 'api_event_discount_get',
        methods: ['GET']
    )]
    public function __invoke(int $id): Response
    {
        $eventDiscount = $this->getEventDiscountsService->getDiscount($id);

        return $this->createSuccessResponse($eventDiscount);
    }
}
