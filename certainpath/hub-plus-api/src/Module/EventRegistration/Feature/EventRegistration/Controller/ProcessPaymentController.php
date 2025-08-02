<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventRegistration\Controller;

use App\Controller\ApiController;
use App\DTO\LoggedInUserDTO;
use App\Module\EventRegistration\Feature\EventRegistration\DTO\Request\ProcessPaymentRequestDTO;
use App\Module\EventRegistration\Feature\EventRegistration\Service\ProcessPaymentService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/private')]
class ProcessPaymentController extends ApiController
{
    public function __construct(
        private readonly ProcessPaymentService $processPaymentService,
    ) {
    }

    #[Route(
        '/payments/process',
        name: 'api_payments_process',
        methods: ['POST']
    )]
    public function __invoke(
        #[MapRequestPayload] ProcessPaymentRequestDTO $requestDTO,
        LoggedInUserDTO $loggedInUserDTO,
    ): Response {
        $result = $this->processPaymentService->processPayment(
            $requestDTO,
            $loggedInUserDTO->getActiveCompany(),
            $loggedInUserDTO->getActiveEmployee()
        );

        return $this->createSuccessResponse($result);
    }
}
