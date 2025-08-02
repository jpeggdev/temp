<?php

namespace App\Services\Customer;

use App\DTO\Request\Customer\UpdateCustomerDoNotMailDTO;
use App\DTO\Request\Prospect\UpdateProspectDoNotMailDTO;
use App\Entity\Customer;
use App\Repository\CustomerRepository;
use App\Services\Prospect\UpdateProspectService;

readonly class UpdateCustomerService
{
    public function __construct(
        private CustomerRepository $customerRepository,
        private UpdateProspectService $prospectService,
    ) {
    }

    public function updateCustomer(int $customerId, UpdateCustomerDoNotMailDTO $dto): Customer
    {

        $customer = $this->customerRepository->findOneByIdOrFail($customerId);

        if ($dto->doNotMail !== null) {
            $customer->setDoNotMail($dto->doNotMail);

            $prospectDTO = new UpdateProspectDoNotMailDTO(
                $dto->doNotMail
            );
            $this->prospectService->updateProspect($customer->getProspect()->getId(), $prospectDTO);
        }

        $this->customerRepository->save($customer);

        return $customer;
    }
}