<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\CompanyDataImportJob;
use App\Exception\CompanyDataImportFileNotFoundException;
use Symfony\Component\HttpFoundation\StreamedResponse;

readonly class DownloadCompanyDataImportJobFileService
{
    public function downloadImportFile(CompanyDataImportJob $importJob): StreamedResponse
    {
        $filePath = $importJob->getFilePath();
        if (!$filePath || !file_exists($filePath) || !is_readable($filePath)) {
            throw new CompanyDataImportFileNotFoundException();
        }

        $extension = pathinfo($filePath, \PATHINFO_EXTENSION) ?: 'csv';
        $contentType = @mime_content_type($filePath) ?: 'application/octet-stream';
        $extLower = strtolower($extension);
        if ('csv' === $extLower) {
            $contentType = 'text/csv';
        } elseif ('xlsx' === $extLower) {
            $contentType = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
        } elseif ('xls' === $extLower) {
            $contentType = 'application/vnd.ms-excel';
        }

        $filename = sprintf('import_%s.%s', $importJob->getUuid(), $extension);

        return new StreamedResponse(
            function () use ($filePath) {
                $stream = fopen($filePath, 'rb');
                fpassthru($stream);
                fclose($stream);
            },
            200,
            [
                'Content-Type' => $contentType,
                'Content-Disposition' => 'attachment; filename="'.$filename.'"',
            ]
        );
    }
}
