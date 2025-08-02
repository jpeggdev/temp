<?php

namespace App\Controller\API\Customers;

use App\Controller\API\ApiController;
use App\Repository\CustomerRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class GetCustomerProspectController extends ApiController
{
    public function __construct(
        private readonly CustomerRepository $customerRepository,
    ) {
    }

    /**
     * Get prospect data for a customer.
     *
     * @param int     $id      Customer ID
     * @param Request $request HTTP request containing intacctId payload
     *
     * @return JsonResponse Prospect data or error response
     */
    #[Route(
        '/api/customers/{id}/prospect',
        name: 'api_customer_prospect_get',
        methods: ['PATCH']
    )]
    public function __invoke(int $id, Request $request): JsonResponse
    {
        $customer = $this->customerRepository->find($id);
        
        if (!$customer) {
            throw $this->createNotFoundException("Customer {$id} not found");
        }

        $prospect = $customer->getProspect();
        
        if (!$prospect) {
            throw $this->createNotFoundException("No prospect found for customer {$id}");
        }

        return $this->createJsonSuccessResponse(
            [
            'id' => $prospect->getId(),
            'fullName' => $prospect->getFullName(),
            'firstName' => $prospect->getFirstName(),
            'lastName' => $prospect->getLastName(),
            'doNotMail' => $prospect->isDoNotMail(),
            'customerId' => $customer->getId(),
            ]
        );
    }
}
