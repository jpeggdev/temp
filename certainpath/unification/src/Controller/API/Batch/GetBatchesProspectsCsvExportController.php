<?php

namespace App\Controller\API\Batch;

use App\Controller\API\ApiController;
use App\DTO\Query\Batch\BatchesProspectsCsvExportDTO;
use App\Exceptions\NotFoundException\BatchStatusNotFoundException;
use App\Generator\BatchesProspectsCsvGenerator;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;

class GetBatchesProspectsCsvExportController extends ApiController
{
    public function __construct(
        private readonly BatchesProspectsCsvGenerator $csvGenerator,
    ) {
    }

    /**
     * @throws BatchStatusNotFoundException
     */
    #[Route(
        '/api/batches/prospects/export/csv',
        name: 'api_batches_prospects_csv_export_get',
        methods: ['GET']
    )]
    public function __invoke(
        #[MapQueryString] BatchesProspectsCsvExportDTO $exportDTO = new BatchesProspectsCsvExportDTO(),
    ): StreamedResponse {
        $fileName = $this->prepareFileName($exportDTO);
        $csvGenerator = $this->csvGenerator->createGenerator($exportDTO);

        return $this->createCsvStreamedResponse(
            $fileName,
            $csvGenerator,
        );
    }

    private function prepareFileName(BatchesProspectsCsvExportDTO $dto): string
    {
        return sprintf(
            'week_%s_year_%s_stochastic.csv',
            $dto->week,
            $dto->year,
        );
    }
}
