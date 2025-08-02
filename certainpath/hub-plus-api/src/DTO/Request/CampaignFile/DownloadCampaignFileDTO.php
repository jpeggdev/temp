<?php

declare(strict_types=1);

namespace App\DTO\Request\CampaignFile;

use Symfony\Component\Validator\Constraints as Assert;

class DownloadCampaignFileDTO
{
    public function __construct(
        #[Assert\NotBlank(message: 'Bucket name is required')]
        public string $bucketName,
        #[Assert\NotBlank(message: 'Object key is required')]
        public string $objectKey,
    ) {
    }
}
