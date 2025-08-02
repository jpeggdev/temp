<?php

declare(strict_types=1);

namespace App\DTO\Response\Company;

use App\Entity\Company;

class MyCompaniesResponseDTO
{
    public string $companyUuid;
    public string $companyName;
    public string $intacctId;

    public function __construct(string $companyUuid, string $companyName, string $intacctId)
    {
        $this->companyUuid = $companyUuid;
        $this->companyName = $companyName;
        $this->intacctId = $intacctId;
    }

    /**
     * Create a list of company DTOs from a collection of Company entities.
     *
     * @param array<Company> $companies
     *
     * @return array<MyCompaniesResponseDTO>
     */
    public static function fromCompanyCollection(array $companies): array
    {
        return array_map(function (Company $company) {
            return new self(
                $company->getUuid(),
                $company->getCompanyName(),
                $company->getIntacctId()
            );
        }, $companies);
    }
}
