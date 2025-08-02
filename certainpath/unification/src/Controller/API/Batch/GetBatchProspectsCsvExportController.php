<?php

namespace App\Controller\API\Batch;

use App\Controller\API\ApiController;
use App\DTO\Query\Prospect\ProspectExportMetadataDTO;
use App\Entity\Batch;
use App\Exceptions\NotFoundException\BatchNotFoundException;
use App\Generator\BatchProspectsCsvGenerator;
use App\Repository\BatchRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;

class GetBatchProspectsCsvExportController extends ApiController
{
    public function __construct(
        private readonly BatchRepository $batchRepository,
        private readonly BatchProspectsCsvGenerator $prospectsCsvGenerator,
    ) {
    }

    /**
     * @throws BatchNotFoundException
     */
    #[Route('/api/batch/{id}/prospects/export/csv', name: 'api_batch_prospects_csv_export_get', methods: ['GET'])]
    public function __invoke(
        int $id,
        #[MapQueryString] ProspectExportMetadataDTO $exportMetadataQueryDTO = new ProspectExportMetadataDTO(),
    ): Response {
        $batch = $this->batchRepository->findByIdOrFail($id);
        $fileName = $this->prepareFileName($batch);
        $csvGenerator = $this->prospectsCsvGenerator->createGenerator($batch, $exportMetadataQueryDTO);

        return $this->createCsvStreamedResponse(
            $fileName,
            $csvGenerator,
        );
    }

    private function prepareFileName(Batch $batch): string
    {
        $fileNamePrefix = 'batch_prospects';
        $timestamp = date('Y-m-d-H:i:s');
        $companyIdentifier = $batch->getCampaign()?->getCompany()?->getIdentifier();

        return sprintf(
            '%s_%s_%s.csv',
            $companyIdentifier, $fileNamePrefix, $timestamp
        );
    }
}
