<?php

declare(strict_types=1);

namespace App\Service\Company;

use App\DTO\Request\Company\CompanyQueryDTO;
use App\DTO\Response\Company\CompanyListResponseDTO;
use App\Entity\Company;
use App\Repository\CompanyRepository;

readonly class CompanyQueryService
{
    public function __construct(private CompanyRepository $companyRepository)
    {
    }

    /**
     * @return array{
     *     companies: CompanyListResponseDTO[],
     *     totalCount: int
     * }
     */
    public function getCompanies(CompanyQueryDTO $queryDto): array
    {
        $companies = $this->companyRepository->findCompaniesByQuery($queryDto);
        $totalCount = $this->companyRepository->getTotalCount($queryDto);

        $companyDtos = array_map(
            static fn (Company $company) => CompanyListResponseDTO::fromEntity($company),
            $companies
        );

        return [
            'companies' => $companyDtos,
            'totalCount' => $totalCount,
        ];
    }
}
