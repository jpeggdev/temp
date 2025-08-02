<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\QuickBooksReport;
use Symfony\Component\HttpFoundation\StreamedResponse;

readonly class DownloadQuickBooksReportService
{
    public function __construct(
        private AmazonS3Service $amazonS3Service,
    ) {
    }

    public function downloadReport(QuickBooksReport $quickBooksReport): StreamedResponse
    {
        // Get the presigned URL and metadata from S3
        $presignedUrl = $this->amazonS3Service->generatePresignedUrl(
            $quickBooksReport->getBucketName(),
            $quickBooksReport->getObjectKey()
        );

        // Fetch metadata to determine content type dynamically
        $contentType = $this->amazonS3Service->getObjectContentType(
            $quickBooksReport->getBucketName(),
            $quickBooksReport->getObjectKey()
        );

        // Create a dynamic filename based on reportType and date
        $filename = sprintf(
            '%s_%s.%s',
            strtolower($quickBooksReport->getReportType()->value),
            $quickBooksReport->getDate()->format('Y-m-d'),
            'xlsx'
        );

        return new StreamedResponse(function () use ($presignedUrl) {
            $stream = fopen($presignedUrl, 'r');
            fpassthru($stream);
            fclose($stream);
        }, 200, [
            'Content-Type' => $contentType,
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }
}
