<?php

namespace App\Transformers;

use App\Entity\CampaignFile;
use League\Fractal\TransformerAbstract;

class CampaignFileTransformer extends TransformerAbstract
{
    public function transform(CampaignFile $campaignFile): array
    {
        $file = $campaignFile->getFile();

        $data['id'] = $campaignFile->getId();
        $data['originalFilename'] = $file?->getOriginalFilename();
        $data['bucketName'] = $file?->getBucketName();
        $data['objectKey'] = $file?->getObjectKey();
        $data['contentType'] = $file?->getContentType();
        $data['mailPackage'] = $this->includeMailPackage($campaignFile);

        return $data;
    }

    private function includeMailPackage(CampaignFile $campaignFile): array
    {
        $mailPackage = $campaignFile->getMailPackage();

        return [
            'id' => $mailPackage?->getId(),
            'name' => $mailPackage?->getName(),
        ];
    }
}
