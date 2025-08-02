<?php

namespace App\Services\UploadService;

use App\Entity\Company;
use App\Exceptions\FileDoesNotExist;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;

abstract readonly class AbstractUploadService
{
    public function __construct(
        protected string $tempDirectory,
        protected LoggerInterface $logger,
    ) {
    }

    /**
     * Move the uploaded file to a subdirectory under the temp directory.
     *
     * @throws FileDoesNotExist
     */
    protected function moveUploadedFile(
        Request $request,
        Company $company,
        string $targetDirectoryName,
    ): string {
        /** @var UploadedFile|null $file */
        $file = $request->files->get('file');

        if (!$file instanceof UploadedFile || !$file->isReadable()) {
            $this->logger->warning('File was not found or not readable.');
            throw new FileDoesNotExist($file ? $file->getRealPath() : '');
        }

        $intacctId = $company->getIdentifier();
        $timestamp = (new \DateTimeImmutable())->format('Y-m-d-H-i-s');
        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $extension = $file->getClientOriginalExtension();

        $newFilename = sprintf('%s_%s.%s', $originalFilename, $timestamp, $extension);
        $targetDirectory = sprintf(
            '%s/%s/%s',
            rtrim($this->tempDirectory, '/'),
            $targetDirectoryName,
            $intacctId
        );

        if (!is_dir($targetDirectory) && !mkdir($targetDirectory, 0777, true) && !is_dir($targetDirectory)) {
            throw new \RuntimeException(sprintf('Directory "%s" was not created', $targetDirectory));
        }

        $uploadedFilePath = $targetDirectory.'/'.$newFilename;

        $file->move($targetDirectory, $newFilename);

        return $uploadedFilePath;
    }
}
