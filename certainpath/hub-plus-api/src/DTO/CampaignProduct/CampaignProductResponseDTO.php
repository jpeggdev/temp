<?php

declare(strict_types=1);

namespace App\DTO\CampaignProduct;

use App\Entity\CampaignProduct;

class CampaignProductResponseDTO
{
    public function __construct(
        public int $id,
        public string $name,
        public string $type,
        public string $description,
        public string $code,
        public string $distributionMethod,
        public string $mailerDescription,
        public bool $hasColoredStock,
        public ?string $category = null,
        public ?string $subCategory = null,
        public ?string $format = null,
        public ?string $prospectPrice = null,
        public ?string $customerPrice = null,
        public ?string $brand = null,
        public ?string $size = null,
        public ?string $targetAudience = null,
        public ?\DateTimeInterface $createdAt = null,
        public ?\DateTimeInterface $updatedAt = null,
    ) {
    }

    public static function fromEntity(CampaignProduct $campaignProduct): self
    {
        return new self(
            $campaignProduct->getId(),
            $campaignProduct->getName(),
            $campaignProduct->getType(),
            $campaignProduct->getDescription(),
            $campaignProduct->getCode(),
            $campaignProduct->getDistributionMethod(),
            $campaignProduct->getMailerDescription(),
            $campaignProduct->hasColoredStock(),
            $campaignProduct->getCategory(),
            $campaignProduct->getSubCategory(),
            $campaignProduct->getFormat(),
            $campaignProduct->getProspectPrice(),
            $campaignProduct->getCustomerPrice(),
            $campaignProduct->getBrand(),
            $campaignProduct->getSize(),
            $campaignProduct->getTargetAudience(),
            $campaignProduct->getCreatedAt(),
            $campaignProduct->getUpdatedAt(),
        );
    }
}
