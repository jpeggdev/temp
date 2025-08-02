<?php

declare(strict_types=1);

namespace App\Service;

use Aws\S3\S3Client;
use Psr\Log\LoggerInterface;

class AmazonS3Service
{
    private S3Client $s3Client;
    private ?LoggerInterface $logger;

    public function __construct(
        string $awsAccessKeyId,
        string $awsSecretAccessKey,
        string $awsDefaultRegion,
        ?LoggerInterface $logger = null,
    ) {
        $this->s3Client = new S3Client([
            'version' => 'latest',
            'region' => $awsDefaultRegion,
            'credentials' => [
                'key' => $awsAccessKeyId,
                'secret' => $awsSecretAccessKey,
            ],
        ]);
        $this->logger = $logger;
    }

    public function uploadFile(string $bucketName, string $fileContent, string $key, string $contentType): string
    {
        $result = $this->s3Client->putObject([
            'Bucket' => $bucketName,
            'Key' => $key,
            'Body' => $fileContent,
            'ContentType' => $contentType,
        ]);

        return $result['ObjectURL'];
    }

    public function generatePresignedUrl(string $bucketName, string $objectKey, int $expiryTime = 3600): string
    {
        $cmd = $this->s3Client->getCommand('GetObject', [
            'Bucket' => $bucketName,
            'Key' => $objectKey,
        ]);

        $request = $this->s3Client->createPresignedRequest($cmd, '+'.$expiryTime.' seconds');

        return (string) $request->getUri();
    }

    /**
     * Generate multiple presigned URLs
     *
     * @param array<string, array{bucketName: string, objectKey: string}> $items
     * @param int $expiryTime
     * @return array<string, string|null> Indexed by the same keys as the input array
     */
    public function generatePresignedUrls(array $items, int $expiryTime = 3600): array
    {
        if (empty($items)) {
            return [];
        }

        $presignedUrls = [];

        foreach ($items as $key => $item) {
            try {
                $presignedUrls[$key] = $this->generatePresignedUrl(
                    $item['bucketName'],
                    $item['objectKey'],
                    $expiryTime
                );
            } catch (\Exception $e) {
                if ($this->logger) {
                    $this->logger->error('Failed to generate presigned URL', [
                        'key' => $key,
                        'bucketName' => $item['bucketName'],
                        'objectKey' => $item['objectKey'],
                        'error' => $e->getMessage()
                    ]);
                }
                $presignedUrls[$key] = null;
            }
        }

        return $presignedUrls;
    }

    public function getObjectContentType(string $bucketName, string $objectKey): string
    {
        $result = $this->s3Client->headObject([
            'Bucket' => $bucketName,
            'Key' => $objectKey,
        ]);

        return $result['ContentType'] ?? 'application/octet-stream';  // Default content type
    }
}
