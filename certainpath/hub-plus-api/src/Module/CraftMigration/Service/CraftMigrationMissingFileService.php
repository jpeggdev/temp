<?php

declare(strict_types=1);

namespace App\Module\CraftMigration\Service;

use App\Module\CraftMigration\DTO\Elements\FileDTO;
use Psr\Log\LoggerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Contracts\HttpClient\HttpClientInterface;

readonly class CraftMigrationMissingFileService
{
    private const string BASE_URL =
        'https://s3.eu-central-003.backblazeb2.com/cdn-assets-servd-host/resolute-grenadier/production';

    public function __construct(
        private CraftMigrationAssetService $assetService,
        private LoggerInterface $logger,
        private Filesystem $filesystem,
        private HttpClientInterface $client,
        private string $tempDirectory,
    ) {
    }

    public function downloadMissingFile(FileDTO $file): bool
    {
        $file = $this->ensureFileDataIsComplete($file);

        if ($this->isFileAvailableLocally($file)) {
            $this->logger->debug('      File already exists locally');

            return true;
        }

        $this->logger->debug(sprintf('      Downloading missing file: %s', $file->volumeFilename));

        return $this->performFileDownload($file);
    }

    public function isFileAvailableLocally(FileDTO $file): bool
    {
        return $this->filesystem->exists($file->localFilename);
    }

    public function getLocalFilePath(FileDTO $file): string
    {
        return sprintf('%s/%s/%s', $this->tempDirectory, 'attachments', ltrim($file->localFilename, '/'));
    }

    private function performFileDownload(FileDTO $file): bool
    {
        // Ensure local directory exists
        $this->ensureLocalDirectoryExists($file);

        try {
            $this->logger->debug(sprintf('      Attempting to download file from S3: %s', $file->remoteFilename));
            $response = $this->client->request(
                'GET',
                $file->remoteFilename
            );
            $content = $response->getContent();

            // Save file content to local filesystem
            $this->filesystem->dumpFile($file->localFilename, $content);

            $this->logger->debug(sprintf('      Successfully downloaded file: %s', $file->localFilename));

            return true;
        } catch (\Throwable $e) {
            $this->logger->error('      Failed to download file from S3', [
                'local_file' => $file->baseFilename,
                'remote_file' => $file->remoteFilename,
                'local_path' => $file->localFilename,
                'error' => $e->getMessage(),
                'exception' => $e,
            ]);

            return false;
        }
    }

    private function getRemoteFileUrl(FileDTO $file): string
    {
        return sprintf('%s/%s', self::BASE_URL, urlencode(ltrim($file->volumeFilename, '/')));
    }

    private function ensureLocalDirectoryExists(FileDTO $file): void
    {
        $localDirectory = dirname($file->localFilename);
        if (!$this->filesystem->exists($localDirectory)) {
            $this->filesystem->mkdir($localDirectory, 0755);
        }
    }

    private function ensureFileDataIsComplete(FileDTO $file): FileDTO
    {
        $file->volumeFilename = $this->assetService->getVolumeFilename($file->localFilename);
        $file->remoteFilename = $this->getRemoteFileUrl($file);
        $file->baseFilename = basename($file->localFilename);

        return $file;
    }
}
