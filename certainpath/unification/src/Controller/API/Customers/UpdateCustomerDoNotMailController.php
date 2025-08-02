<?php

namespace App\Controller\API\Customers;

use App\Controller\API\ApiController;
use App\DTO\Request\Customer\UpdateCustomerDoNotMailDTO;
use App\DTO\Request\Prospect\UpdateProspectDoNotMailDTO;
use App\Services\Customer\UpdateCustomerService;
use App\Services\Prospect\UpdateProspectService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

class UpdateCustomerDoNotMailController extends ApiController
{
    public function __construct(
        private readonly UpdateCustomerService $updateCustomerService,
    ) {
    }

    #[Route('/api/customers/{id}/do-not-mail', name: 'api_customer_patch_do_not_mail', methods: ['PATCH', 'POST'])]
    public function __invoke(
        int $id,
        #[MapRequestPayload] UpdateCustomerDoNotMailDTO $updateCustomerDoNotMailDTO
    ): Response {
        $customer = $this->updateCustomerService->updateCustomer($id, $updateCustomerDoNotMailDTO);

        return $this->createJsonSuccessResponse([
            'id' => $customer->getId(),
            'doNotMail' => $customer->isDoNotMail(),
        ]);
    }
}