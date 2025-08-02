<?php

namespace App\Controller\API\Customers;

use App\Controller\API\ApiController;
use App\DTO\Request\Customer\CustomerQueryDTO;
use App\Services\CustomerQueryService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;

class GetCustomersController extends ApiController
{
    public function __construct(private readonly CustomerQueryService $customerQueryService)
    {
    }

    #[Route('/api/customers', name: 'api_customers_get', methods: ['GET'])]
    public function __invoke(
        #[MapQueryString] CustomerQueryDTO $queryDto,
        Request $request,
    ): Response {
        $customersData = $this->customerQueryService->getCustomers($queryDto);

        return $this->createJsonSuccessResponse(
            $customersData['customers'],
            $customersData
        );
    }
}
