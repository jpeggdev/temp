<?php

namespace App\ValueObject;

trait CampaignProductFieldsTrait
{
    public ?string $name = null;
    public ?string $type = null;
    public ?string $description = null;
    public ?string $category = null;
    public ?string $subCategory = null;
    public ?string $format = null;
    public ?string $prospectPrice = null;
    public ?string $customerPrice = null;
    public ?string $mailerDescription = null;
    public ?string $code = null;
    public ?string $hasColoredStock = null;
    public ?string $brand = null;
    public ?string $size = null;
    public ?string $distributionMethod = null;
    public ?string $targetAudience = null;
    public ?string $meta = null;
}
