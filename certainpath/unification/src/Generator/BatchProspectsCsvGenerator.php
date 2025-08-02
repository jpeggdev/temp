<?php

namespace App\Generator;

use App\DTO\Query\Prospect\ProspectExportMetadataDTO;
use App\Entity\Batch;
use App\Services\ProspectExportService;
use Generator;

readonly class BatchProspectsCsvGenerator
{
    private const CSV_EXPORT_BATCH_ROWS_SIZE = 100;

    public function __construct(
        private ProspectExportService $prospectExportService,
    ) {
    }

    public function createGenerator(
        Batch $batch,
        ProspectExportMetadataDTO $exportMetadataQueryDTO
    ): Generator {
        yield $this->prospectExportService->getHeaders();

        $rows = [];
        $metadata = $this->prepareMetadata($batch, $exportMetadataQueryDTO);

        foreach ($batch->getProspects() as $prospect) {
            $rows[] = $this->prospectExportService->prepareRow($prospect, $metadata);

            if (count($rows) >= self::CSV_EXPORT_BATCH_ROWS_SIZE) {
                foreach ($rows as $row) {
                    yield $row;
                }

                $rows = [];
            }
        }

        if ($rows) {
            foreach ($rows as $row) {
                yield $row;
            }
        }
    }

    private function prepareMetadata(Batch $batch, ProspectExportMetadataDTO $metadataDTO): array
    {
        return [
            'Job Number' => $metadataDTO->jobNumber ?: $batch->getId(),
            'Ring To' => $metadataDTO->ringTo ?: $batch->getCampaign()?->getPhoneNumber(),
            'Version Code' => $metadataDTO->versionCode ?: $batch->getCampaign()?->getId(),
            'CSR Full Name' => $metadataDTO->csr ?: '',
        ];
    }
}
