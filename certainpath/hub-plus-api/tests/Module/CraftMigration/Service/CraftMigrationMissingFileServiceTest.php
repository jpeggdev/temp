<?php

declare(strict_types=1);

namespace App\Tests\Module\CraftMigration\Service;

use App\Module\CraftMigration\DTO\Elements\FileDTO;
use App\Module\CraftMigration\Repository\CraftMigrationRepository;
use App\Module\CraftMigration\Service\CraftMigrationAssetService;
use App\Module\CraftMigration\Service\CraftMigrationMissingFileService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class CraftMigrationMissingFileServiceTest extends TestCase
{
    private CraftMigrationAssetService $assetService;
    private CraftMigrationMissingFileService $service;
    private MockObject&LoggerInterface $logger;
    private MockObject&Filesystem $filesystem;
    private MockObject&HttpClientInterface $httpClient;

    protected function setUp(): void
    {
        // Create real asset service with mocked repository
        $mockRepository = $this->createMock(CraftMigrationRepository::class);
        $this->assetService = new CraftMigrationAssetService($mockRepository, '/tmp/test');

        $this->logger = $this->createMock(LoggerInterface::class);
        $this->filesystem = $this->createMock(Filesystem::class);
        $this->httpClient = $this->createMock(HttpClientInterface::class);

        $this->service = new CraftMigrationMissingFileService(
            $this->assetService,
            $this->logger,
            $this->filesystem,
            $this->httpClient,
            '/tmp/test'
        );
    }

    public function testIsFileAvailableLocallyReturnsTrueWhenFileExists(): void
    {
        $file = new FileDTO(
            fileId: 123,
            baseFilename: 'image.jpg',
            localFilename: '/tmp/test/attachments/test/image.jpg',
            remoteFilename: 'https://example.com/image.jpg',
            volumeFilename: 'test/image.jpg'
        );

        $this->filesystem
            ->expects($this->once())
            ->method('exists')
            ->with($file->localFilename)
            ->willReturn(true);

        $result = $this->service->isFileAvailableLocally($file);

        $this->assertTrue($result);
    }

    public function testIsFileAvailableLocallyReturnsFalseWhenFileDoesNotExist(): void
    {
        $file = new FileDTO(
            fileId: 124,
            baseFilename: 'missing.jpg',
            localFilename: '/tmp/test/attachments/test/missing.jpg',
            remoteFilename: 'https://example.com/missing.jpg',
            volumeFilename: 'test/missing.jpg'
        );

        $this->filesystem
            ->expects($this->once())
            ->method('exists')
            ->with($file->localFilename)
            ->willReturn(false);

        $result = $this->service->isFileAvailableLocally($file);

        $this->assertFalse($result);
    }

    public function testGetLocalFilePathConstructsCorrectPath(): void
    {
        $testCases = [
            'test/image.jpg' => '/tmp/test/attachments/test/image.jpg',
            '/test/image.jpg' => '/tmp/test/attachments/test/image.jpg',
            'image.jpg' => '/tmp/test/attachments/image.jpg',
            '/image.jpg' => '/tmp/test/attachments/image.jpg',
        ];

        foreach ($testCases as $input => $expected) {
            $file = new FileDTO(
                fileId: 125,
                baseFilename: basename($input),
                localFilename: $input,
                remoteFilename: 'https://example.com/'.ltrim($input, '/'),
                volumeFilename: ltrim($input, '/')
            );
            $result = $this->service->getLocalFilePath($file);
            $this->assertEquals($expected, $result, "Failed for input: $input");
        }
    }

    public function testDownloadMissingFileReturnsTrueWhenFileExistsLocally(): void
    {
        $file = new FileDTO(
            fileId: 126,
            baseFilename: 'image.jpg',
            localFilename: '/tmp/test/attachments/test/image.jpg',
            remoteFilename: 'https://example.com/image.jpg',
            volumeFilename: 'test/image.jpg'
        );

        $this->filesystem
            ->expects($this->once())
            ->method('exists')
            ->with($file->localFilename)
            ->willReturn(true);

        $this->logger
            ->expects($this->once())
            ->method('debug')
            ->with('      File already exists locally');

        $this->httpClient
            ->expects($this->never())
            ->method('request');

        $result = $this->service->downloadMissingFile($file);

        $this->assertTrue($result);
    }

    public function testDownloadMissingFileSuccessfullyDownloadsFile(): void
    {
        $file = new FileDTO(
            fileId: 127,
            baseFilename: 'image.jpg',
            localFilename: '/tmp/test/attachments/test/image.jpg',
            remoteFilename: '',
            volumeFilename: ''
        );
        $fileContent = 'mock file content';
        $expectedDirectory = '/tmp/test/attachments/test';
        $expectedRemoteUrl = 'https://s3.eu-central-003.backblazeb2.com/cdn-assets-servd-host/resolute-grenadier/production/test%2Fimage.jpg';

        $mockResponse = $this->createMock(ResponseInterface::class);
        $mockResponse
            ->expects($this->once())
            ->method('getContent')
            ->willReturn($fileContent);

        // File doesn't exist locally
        $this->filesystem
            ->expects($this->exactly(2))
            ->method('exists')
            ->willReturnMap([
                [$file->localFilename, false],
                [$expectedDirectory, false],
            ]);

        // Directory creation
        $this->filesystem
            ->expects($this->once())
            ->method('mkdir')
            ->with($expectedDirectory, 0755);

        // File download
        $this->httpClient
            ->expects($this->once())
            ->method('request')
            ->with('GET', $expectedRemoteUrl)
            ->willReturn($mockResponse);

        // File write
        $this->filesystem
            ->expects($this->once())
            ->method('dumpFile')
            ->with($file->localFilename, $fileContent);

        $this->logger
            ->expects($this->exactly(3))
            ->method('debug')
            ->withConsecutive(
                [sprintf('      Downloading missing file: %s', '/test/image.jpg')],
                [sprintf('      Attempting to download file from S3: %s', $expectedRemoteUrl)],
                [sprintf('      Successfully downloaded file: %s', $file->localFilename)]
            );

        $result = $this->service->downloadMissingFile($file);

        $this->assertTrue($result);
    }

    public function testDownloadMissingFileHandlesHttpException(): void
    {
        $file = new FileDTO(
            fileId: 128,
            baseFilename: 'image.jpg',
            localFilename: '/tmp/test/attachments/test/image.jpg',
            remoteFilename: '',
            volumeFilename: ''
        );
        $expectedDirectory = '/tmp/test/attachments/test';
        $expectedRemoteUrl = 'https://s3.eu-central-003.backblazeb2.com/cdn-assets-servd-host/resolute-grenadier/production/test%2Fimage.jpg';
        $exception = new \Exception('HTTP download failed');

        // File doesn't exist locally
        $this->filesystem
            ->expects($this->exactly(2))
            ->method('exists')
            ->willReturnMap([
                [$file->localFilename, false],
                [$expectedDirectory, false],
            ]);

        // Directory creation
        $this->filesystem
            ->expects($this->once())
            ->method('mkdir')
            ->with($expectedDirectory, 0755);

        // File download fails
        $this->httpClient
            ->expects($this->once())
            ->method('request')
            ->with('GET', $expectedRemoteUrl)
            ->willThrowException($exception);

        $this->logger
            ->expects($this->exactly(2))
            ->method('debug')
            ->withConsecutive(
                [sprintf('      Downloading missing file: %s', '/test/image.jpg')],
                [sprintf('      Attempting to download file from S3: %s', $expectedRemoteUrl)]
            );

        $this->logger
            ->expects($this->once())
            ->method('error')
            ->with('      Failed to download file from S3', [
                'local_file' => 'image.jpg',
                'remote_file' => $expectedRemoteUrl,
                'local_path' => $file->localFilename,
                'error' => 'HTTP download failed',
                'exception' => $exception,
            ]);

        $result = $this->service->downloadMissingFile($file);

        $this->assertFalse($result);
    }
}
