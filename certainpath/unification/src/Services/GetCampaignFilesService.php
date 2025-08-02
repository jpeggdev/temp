<?php

declare(strict_types=1);

namespace App\Services;

use App\DTO\Query\Campaign\CampaignFileQueryDTO;
use App\DTO\Response\CampaignFileListResponseDTO;
use App\Entity\Campaign;
use App\Entity\CampaignFile;
use App\Repository\CampaignFileRepository;

readonly class GetCampaignFilesService
{
    public function __construct(private CampaignFileRepository $campaignFileRepository)
    {
    }

    /**
     * @return array{
     *     files: CampaignFileListResponseDTO[],
     *     total: int,
     *     currentPage: int,
     *     perPage: int
     * }
     */
    public function getFiles(Campaign $campaign, CampaignFileQueryDTO $queryDto): array
    {
        $files = $this->campaignFileRepository->findFilesByQuery($campaign, $queryDto);
        $totalCount = $this->campaignFileRepository->getTotalCount($campaign, $queryDto);

        $fileDtos = array_map(
            fn (CampaignFile $file) => CampaignFileListResponseDTO::fromEntity($file),
            $files
        );

        return [
            'files' => $fileDtos,
            'total' => $totalCount,
            'currentPage' => $queryDto->page,
            'perPage' => $queryDto->pageSize,
        ];
    }
}
