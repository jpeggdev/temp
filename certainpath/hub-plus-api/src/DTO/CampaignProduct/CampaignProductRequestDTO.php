<?php

namespace App\DTO\CampaignProduct;

class CampaignProductRequestDTO
{
    public string $name;
    public ?string $type = null;
    public string $description;
    public string $category;
    public ?string $subCategory = null;
    public ?string $format = null;
    public float $prospectPrice;
    public float $customerPrice;
    public ?string $mailerDescription = null;
    public ?string $code = null;
    public bool $hasColoredStock;
    public ?string $brand = null;
    public ?string $size = null;
    public ?string $distributionMethod = null;
    public ?string $targetAudience = null;
}
