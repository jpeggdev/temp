<?php

declare(strict_types=1);

namespace App\Services;

use App\DTO\Request\Customer\CustomerQueryDTO;
use App\DTO\Response\CustomerListResponseDTO;
use App\Entity\Customer;
use App\Repository\CustomerRepository;

readonly class CustomerQueryService
{
    public function __construct(private CustomerRepository $customerRepository)
    {
    }

    /**
     * @return array{
     *     customers: CustomerListResponseDTO[],
     *     total: int,
     *     currentPage: int,
     *     perPage: int
     * }
     */
    public function getCustomers(CustomerQueryDTO $queryDto): array
    {
        $customers = $this->customerRepository->findCustomersByQuery($queryDto);
        $totalCount = $this->customerRepository->getTotalCount($queryDto);

        $customerDtos = array_map(
            static fn (Customer $customer) => CustomerListResponseDTO::fromEntity($customer),
            $customers
        );

        return [
            'customers' => $customerDtos,
            'total' => $totalCount,
            'currentPage' => $queryDto->page,
            'perPage' => $queryDto->pageSize,
        ];
    }
}
