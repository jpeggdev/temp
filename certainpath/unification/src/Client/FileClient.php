<?php

namespace App\Client;

use App\Exceptions\FileCouldNotBeRetrieved;
use App\ValueObjects\TempFile;
use Aws\S3\S3Client;
use Exception;
use RuntimeException;

class FileClient
{
    private S3Client $s3Client;

    public function __construct(
        private readonly string $awsAccessKeyId,
        private readonly string $awsSecretAccessKey,
        private readonly string $tempDirectory
    ) {
        $this->s3Client = new S3Client(
            [
                'version' => 'latest',
                'region' => 'us-east-1',
                'credentials' => [
                    'key' => $this->awsAccessKeyId,
                    'secret' => $this->awsSecretAccessKey
                ]
            ]
        );
    }

    public function list(
        string $bucket,
        string $prefix
    ): array {
        $result = $this->s3Client->listObjects(
            [
                'Bucket' => $bucket,
                'Prefix' => $prefix
            ]
        );
        $fileList = [];
        if ($result && isset($result['Contents'])) {
            foreach ($result['Contents'] as $result) {
                $key = $result['Key'];
                $fileList[] = $key;
            }
        }
        return $fileList;
    }

    /**
     * @throws FileCouldNotBeRetrieved
     */
    public function download(
        string $bucket,
        string $relativeFilePath
    ): string {
        $tempFilePath = $this->tempDirectory . '/' . $relativeFilePath;
        $tempDirectory = dirname($tempFilePath);
        if (!is_dir($tempDirectory) && !mkdir($tempDirectory, 0777, true) && !is_dir($tempDirectory)) {
            throw new RuntimeException(sprintf('Directory "%s" was not created', $tempDirectory));
        }
        try {
            $this->s3Client->getObject(
                [
                    'Bucket' => $bucket,
                    'Key' => $relativeFilePath,
                    'SaveAs' => $tempFilePath
                ]
            );
        } catch (Exception $e) {
            throw new FileCouldNotBeRetrieved(
                'File: ' . $relativeFilePath . ' - ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
        return $tempFilePath;
    }

    public function upload(
        string $bucket,
        TempFile $tempFile
    ): void {
        $this->s3Client->putObject(
            [
                'Bucket' => $bucket,
                'Key' => $tempFile->getRelativePath(),
                'SourceFile' => $tempFile->getFullPath()
            ]
        );
    }
}
