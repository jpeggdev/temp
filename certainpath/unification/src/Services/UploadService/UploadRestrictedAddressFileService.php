<?php

namespace App\Services\UploadService;

use App\Exceptions\FileDoesNotExist;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;

readonly class UploadRestrictedAddressFileService
{
    public function __construct(
        private LoggerInterface $logger,
    ) {
    }

    /**
     * @throws FileDoesNotExist
     */
    public function handle(Request $request): int
    {
        $file = $request->files->get('file');

        if (!$file instanceof UploadedFile || !$file->isReadable()) {
            $this->logger->warning('File was not found or not readable.');
            throw new FileDoesNotExist($file ? $file->getRealPath() : '');
        }

        return 0;
    }
}
