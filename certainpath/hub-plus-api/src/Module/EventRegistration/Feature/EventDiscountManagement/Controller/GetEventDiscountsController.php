<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventDiscountManagement\Controller;

use App\Controller\ApiController;
use App\Module\EventRegistration\Feature\EventDiscountManagement\DTO\Query\GetEventDiscountsDTO;
use App\Module\EventRegistration\Feature\EventDiscountManagement\Service\GetEventDiscountsService;
use App\Module\EventRegistration\Feature\EventDiscountManagement\Voter\EventDiscountVoter;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/private')]
class GetEventDiscountsController extends ApiController
{
    public function __construct(
        private readonly GetEventDiscountsService $getEventDiscountsService,
    ) {
    }

    #[Route(
        '/event-discounts',
        name: 'api_event_discounts_get',
        methods: ['GET']
    )]
    public function __invoke(
        #[MapQueryString] GetEventDiscountsDTO $queryDto,
    ): Response {
        $this->denyAccessUnlessGranted(EventDiscountVoter::READ);
        $eventDiscountsData = $this->getEventDiscountsService->getDiscounts($queryDto);

        return $this->createSuccessResponse(
            $eventDiscountsData['eventDiscounts'],
            $eventDiscountsData['totalCount']
        );
    }
}
