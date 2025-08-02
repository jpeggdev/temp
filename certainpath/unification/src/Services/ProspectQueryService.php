<?php

declare(strict_types=1);

namespace App\Services;

use App\DTO\Query\Prospect\ProspectQueryDTO;
use App\DTO\Response\ProspectListResponseDTO;
use App\Entity\Prospect;
use App\Repository\ProspectRepository;

readonly class ProspectQueryService
{
    public function __construct(private ProspectRepository $prospectRepository)
    {
    }

    /**
     * @return array{
     *     prospects: ProspectListResponseDTO[],
     *     totalCount: int
     * }
     */
    public function getProspects(ProspectQueryDTO $queryDto): array
    {
        $prospects = $this->prospectRepository->findNonCustomerProspectsByQuery($queryDto);
        $totalCount = $this->prospectRepository->getTotalCount($queryDto);

        $prospectDTOs = array_map(
            static fn (Prospect $prospect) => ProspectListResponseDTO::fromEntity($prospect),
            $prospects
        );

        return [
            'prospects' => $prospectDTOs,
            'total' => $totalCount,
            'currentPage' => $queryDto->page,
            'perPage' => $queryDto->pageSize,
        ];
    }
}
