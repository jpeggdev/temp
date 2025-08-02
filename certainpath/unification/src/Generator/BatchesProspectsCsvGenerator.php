<?php

namespace App\Generator;

use App\DTO\Query\Batch\BatchesProspectsCsvExportDTO;
use App\Entity\Batch;
use App\Entity\BatchStatus;
use App\Exceptions\InvalidArgumentException\InvalidDateFormatException;
use App\Exceptions\NotFoundException\BatchStatusNotFoundException;
use App\Repository\BatchRepository;
use App\Repository\BatchStatusRepository;
use App\Repository\ProspectRepository;
use App\Services\ProspectExportService;
use Carbon\Carbon;
use Generator;

readonly class BatchesProspectsCsvGenerator
{
    private const CSV_BATCH_ROWS_SIZE = 100;

    public function __construct(
        private BatchRepository $batchRepository,
        private ProspectRepository $prospectRepository,
        private ProspectExportService $prospectExportService,
        private BatchStatusRepository $batchStatusRepository,
    ) {
    }

    /**
     * @throws BatchStatusNotFoundException
     */
    public function createGenerator(BatchesProspectsCsvExportDTO $dto): Generator
    {
        $startDate = $this->getCarbonDateFromWeekAndYear($dto->week, $dto->year)->startOfWeek();
        $endDate = $this->getCarbonDateFromWeekAndYear($dto->week, $dto->year)->endOfWeek();

        $generatorKey = 0;
        yield $generatorKey => $this->prospectExportService->getHeaders();

        $batchStatusNew = $this->batchStatusRepository->findOneByNameOrFail(BatchStatus::STATUS_NEW);
        $batches = $this->batchRepository->fetchAllByStatusAndWeekStartAndEndDatesQueryBuilder(
            $batchStatusNew,
            $startDate,
            $endDate
        );

        foreach ($batches as $batch) {
            $rows = [];
            $metadata = $this->prepareMetadata($batch);
            $prospectsFiltered = $this->prospectRepository->fetchAllByBatchId($batch->getId());

            foreach ($prospectsFiltered as $prospect) {
                $rows[] = $this->prospectExportService->prepareRow($prospect, $metadata);

                if (count($rows) >= self::CSV_BATCH_ROWS_SIZE) {
                    foreach ($rows as $row) {
                        yield ++$generatorKey => $row;
                    }

                    $rows = [];
                }
            }

            foreach ($rows as $row) {
                yield ++$generatorKey => $row;
            }
        }
    }

    private function prepareMetadata(Batch $batch): array
    {
        return [
            'Job Number' => $batch->getId(),
            'Ring To' => $batch->getCampaign()?->getPhoneNumber(),
            'Version Code' => $batch->getCampaign()?->getId(),
            'CSR Full Name' => null,
        ];
    }

    private function getCarbonDateFromWeekAndYear(int $week, int $year): Carbon
    {
        $carbonDate = Carbon::now()->setISODate($year, $week)->startOfDay();

        if (!$carbonDate instanceof Carbon) {
            $message = sprintf(
                'Unable to create a date with the provided arguments: week %d, year %d.',
                $week, $year,
            );

            throw new InvalidDateFormatException('', $message);
        }

        return $carbonDate;
    }
}
