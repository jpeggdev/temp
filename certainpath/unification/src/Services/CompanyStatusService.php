<?php

namespace App\Services;

use App\DTO\Domain\StatusDTO;
use App\Entity\Company;
use App\Exceptions\NotFoundException\CompanyNotFoundException;
use App\Repository\CompanyRepository;

class CompanyStatusService
{
    public function __construct(
        private readonly TenantStreamAuditService $tenantStreamAuditService,
        private readonly CompanyJobService $companyJobService,
        private readonly CompanyRepository $companyRepository,
    ) {
    }

    public function getCompanyStatus(
        Company $company
    ): StatusDTO {
        $statusKeyValues = [
            'Jobs Running' =>
                $this->companyJobService->getCompanyActiveJobEventCount($company),
            'Prospects to Process' =>
                $this->tenantStreamAuditService->getProspectsCount($company->getIdentifier()),
            'Customers to Process' =>
                $this->tenantStreamAuditService->getMembersCount($company->getIdentifier()),
            'Invoices to Process' =>
                $this->tenantStreamAuditService->getInvoiceCount($company->getIdentifier()),
        ];
        return new StatusDTO(
            $statusKeyValues
        );
    }

    /**
     * @throws CompanyNotFoundException
     */
    public function getCompanyStatusByIdentifier(
        string $identifier
    ): StatusDTO {
        $company = $this->companyRepository->findActiveByIdentifierOrCreate(
            $identifier
        );
        if (!$company) {
            throw new CompanyNotFoundException(
                $identifier
            );
        }
        return $this->getCompanyStatus($company);
    }
}
