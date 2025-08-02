<?php

namespace App\Client;

use Aws\S3\S3Client;

class FileClient
{
    private S3Client $s3Client;

    public function __construct(
        private readonly string $awsAccessKeyId,
        private readonly string $awsSecretAccessKey,
        private readonly string $tempDirectory,
    ) {
        $this->s3Client = new S3Client(
            [
                'version' => 'latest',
                'region' => 'us-east-1',
                'credentials' => [
                    'key' => $this->awsAccessKeyId,
                    'secret' => $this->awsSecretAccessKey,
                ],
            ]
        );
    }

    public function list(
        string $bucket,
        string $prefix,
    ): array {
        $result = $this->s3Client->listObjects(
            [
                'Bucket' => $bucket,
                'Prefix' => $prefix,
            ]
        );
        $fileList = [];
        foreach ($result['Contents'] as $result) {
            $key = $result['Key'];
            $fileList[] = $key;
        }

        return $fileList;
    }

    public function download(string $bucket, string $file): string
    {
        $tempFilePath = $this->tempDirectory.'/'.$file;
        $tempDirectory = dirname($tempFilePath);
        if (!is_dir($tempDirectory) && !mkdir($tempDirectory, 0777, true) && !is_dir($tempDirectory)) {
            throw new \RuntimeException(sprintf('Directory "%s" was not created', $tempDirectory));
        }
        $this->s3Client->getObject(
            [
                'Bucket' => $bucket,
                'Key' => $file,
                'SaveAs' => $tempFilePath,
            ]
        );

        return $tempFilePath;
    }
}
