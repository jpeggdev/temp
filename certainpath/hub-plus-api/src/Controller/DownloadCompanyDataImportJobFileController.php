<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\CompanyDataImportJob;
use App\Service\DownloadCompanyDataImportJobFileService;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/private')]
class DownloadCompanyDataImportJobFileController extends ApiController
{
    public function __construct(
        private readonly DownloadCompanyDataImportJobFileService $downloadService,
    ) {
    }

    #[Route(
        '/download-company-data-import-job-file/{uuid}',
        name: 'api_company_data_import_job_file_download',
        methods: ['GET']
    )]
    public function __invoke(CompanyDataImportJob $importJob): StreamedResponse
    {
        return $this->downloadService->downloadImportFile($importJob);
    }
}
