<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\Request\FieldServiceExportQueryDTO;
use App\DTO\Response\FieldServiceExportResponseDTO;
use App\Entity\Company;
use App\Repository\FieldServiceExportRepository;

readonly class FieldServiceExportQueryService
{
    public function __construct(private FieldServiceExportRepository $exportRepository)
    {
    }

    /**
     * @return array{
     *     exports: FieldServiceExportResponseDTO[],
     *     totalCount: int
     * }
     */
    public function getExports(Company $company, FieldServiceExportQueryDTO $queryDto): array
    {
        $exports = $this->exportRepository->findExports(
            $company->getIntacctId(),
            $queryDto->page,
            $queryDto->pageSize,
            $queryDto->sortOrder
        );

        $exportDtos = array_map(
            fn ($export) => FieldServiceExportResponseDTO::fromFieldServiceExport($export),
            $exports['exports']
        );

        return [
            'exports' => $exportDtos,
            'totalCount' => $exports['totalCount'],
        ];
    }
}
