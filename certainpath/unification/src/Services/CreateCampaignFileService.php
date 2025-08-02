<?php

namespace App\Services;

use App\DTO\Request\Campaign\CreateCampaignFileDTO;
use App\Entity\Campaign;
use App\Entity\CampaignFile;
use App\Entity\File;
use App\Exceptions\NotFoundException\MailPackageNotFoundException;
use Doctrine\ORM\EntityManagerInterface;

readonly class CreateCampaignFileService
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    /**
     * @throws MailPackageNotFoundException
     */
    public function createFile(
        Campaign $campaign,
        CreateCampaignFileDTO $fileDto
    ): CampaignFile {
        $mailPackage = $campaign->getMailPackage();
        if ($mailPackage === null) {
            throw new MailPackageNotFoundException();
        }

        $file = (new File())
            ->setOriginalFilename($fileDto->originalFilename)
            ->setBucketName($fileDto->bucketName)
            ->setObjectKey($fileDto->objectKey)
            ->setContentType($fileDto->contentType);

        $campaignFile = (new CampaignFile())
            ->setFile($file)
            ->setCampaign($campaign)
            ->setMailPackage($mailPackage);

        $this->entityManager->persist($file);
        $this->entityManager->persist($campaignFile);
        $this->entityManager->flush();

        return $campaignFile;
    }
}
