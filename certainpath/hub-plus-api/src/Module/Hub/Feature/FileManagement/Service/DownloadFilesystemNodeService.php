<?php

declare(strict_types=1);

namespace App\Module\Hub\Feature\FileManagement\Service;

use App\Entity\File;
use App\Service\AmazonS3Service;
use Symfony\Component\HttpFoundation\StreamedResponse;

readonly class DownloadFilesystemNodeService
{
    public function __construct(
        private AmazonS3Service $amazonS3Service,
    ) {
    }

    public function downloadFile(File $file): StreamedResponse
    {
        $presignedUrl = $this->amazonS3Service->generatePresignedUrl(
            $file->getBucketName(),
            $file->getObjectKey()
        );

        $contentType = $file->getContentType() ?? 'application/octet-stream';
        $filename = $file->getOriginalFilename() ?? $file->getName() ?? 'download.bin';

        return new StreamedResponse(function () use ($presignedUrl) {
            $stream = fopen($presignedUrl, 'r');
            if ($stream) {
                fpassthru($stream);
                fclose($stream);
            }
        }, 200, [
            'Content-Type' => $contentType,
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }
}
