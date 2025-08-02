<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventVoucherManagement\Controller;

use App\Controller\ApiController;
use App\Module\EventRegistration\Feature\EventVoucherManagement\DTO\Query\GetEventVouchersDTO;
use App\Module\EventRegistration\Feature\EventVoucherManagement\Service\GetEventVoucherService;
use App\Module\EventRegistration\Feature\EventVoucherManagement\Voter\EventVoucherVoter;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/private')]
class GetEventVouchersController extends ApiController
{
    public function __construct(
        private readonly GetEventVoucherService $getEventVoucherService,
    ) {
    }

    #[Route('/event-vouchers', name: 'api_event_vouchers_get', methods: ['GET'])]
    public function __invoke(#[MapQueryString] GetEventVouchersDTO $queryDto): Response
    {
        $this->denyAccessUnlessGranted(EventVoucherVoter::READ);
        $vouchersData = $this->getEventVoucherService->getVouchers($queryDto);

        return $this->createSuccessResponse(
            $vouchersData['eventVouchers'],
            $vouchersData['totalCount']
        );
    }
}
