<?php

namespace App\Generator;

use App\DTO\Domain\ProspectFilterRulesDTO;
use App\DTO\Query\Prospect\ProspectExportMetadataDTO;
use App\Exceptions\NotFoundException\ProspectFilterRuleNotFoundException;
use App\Repository\ProspectRepository;
use App\Services\ProspectExportService;
use Generator;

readonly class CompanyProspectsCsvGenerator
{
    private const CSV_EXPORT_BATCH_ROWS_SIZE = 100;

    public function __construct(
        private ProspectRepository $prospectRepository,
        private ProspectExportService $prospectExportService,
    ) {
    }

    /**
     * @throws ProspectFilterRuleNotFoundException
     */
    public function createGenerator(
        ProspectFilterRulesDTO $filterRulesDTO,
        ProspectExportMetadataDTO $exportMetadataQueryDTO
    ): Generator {
        yield $this->prospectExportService->getHeaders();

        $rows = [];
        $metadata = $this->prepareMetadata($exportMetadataQueryDTO);
        $prospectsFiltered = $this->prospectRepository->fetchAllByProspectFilterRulesDTO($filterRulesDTO);

        foreach ($prospectsFiltered as $prospect) {
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

    private function prepareMetadata(ProspectExportMetadataDTO $metadataDTO): array
    {
        return [
            'Job Number' => $metadataDTO->jobNumber ?: '',
            'Ring To' => $metadataDTO->ringTo ?: '',
            'Version Code' => $metadataDTO->versionCode ?: '',
            'CSR Full Name' => $metadataDTO->csr ?: '',
        ];
    }
}
