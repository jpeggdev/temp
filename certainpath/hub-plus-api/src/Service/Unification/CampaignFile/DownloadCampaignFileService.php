<?php

declare(strict_types=1);

namespace App\Service\Unification\CampaignFile;

use App\Service\AmazonS3Service;
use Symfony\Component\HttpFoundation\StreamedResponse;

readonly class DownloadCampaignFileService
{
    public function __construct(private AmazonS3Service $amazonS3Service)
    {
    }

    public function downloadFile(string $bucketName, string $objectKey): StreamedResponse
    {
        $presignedUrl = $this->amazonS3Service->generatePresignedUrl($bucketName, $objectKey);

        $contentType = $this->amazonS3Service->getObjectContentType($bucketName, $objectKey);

        $filename = basename($objectKey);

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
