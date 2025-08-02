<?php

namespace App\Entity;

use App\Repository\CampaignProductRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CampaignProductRepository::class)]
#[ORM\Table(name: 'campaign_product')]
#[ORM\HasLifecycleCallbacks]
#[ORM\UniqueConstraint(fields: ['name'])]
class CampaignProduct
{
    use Trait\TimestampableDateTimeTZTrait;

    public const array CAMPAIGN_PRODUCT_TYPES = [
        'service',
    ];

    public const array CAMPAIGN_PRODUCT_CATEGORIES = [
        'letters',
        'postcards',
        'misc',
    ];

    public const array CAMPAIGN_PRODUCT_SUBCATEGORIES = [
        'standard',
        'specialty',
        'promotional',
    ];

    public const array CAMPAIGN_PRODUCT_CODES = [
        'LTR',
        'PC',
    ];

    public const array CAMPAIGN_PRODUCT_DISTRIBUTION_METHODS = [
        'direct_mail',
        'eddm',
    ];

    public const array CAMPAIGN_PRODUCT_TARGET_AUDIENCES = [
        'prospects',
        'customers',
        'prospects_and_customers',
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT, nullable: false)]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT, nullable: false, options: ['default' => 'service'])]
    private ?string $type = null;

    #[ORM\Column(type: Types::TEXT, nullable: false)]
    private ?string $description = null;

    /*
     * letters, postcards, misc
     */
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $category = null;

    /*
     * standard, specialty, promotional
     */
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $subCategory = null;

    /*
     * #10 Envelope, Buy Back 6x9 Envelope, Standard Size, Variable
     */
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $format = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 12, scale: 5, nullable: true)]
    private ?string $prospectPrice = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 12, scale: 5, nullable: true)]
    private ?string $customerPrice = null;

    #[ORM\Column(type: Types::TEXT, nullable: false)]
    private ?string $mailerDescription = null;

    /*
     * LTR (letter), PC (postcard)
     */
    #[ORM\Column(type: Types::TEXT)]
    private ?string $code = null;

    #[ORM\Column]
    private ?bool $hasColoredStock = null;

    /*
     * Cornerstone, Fix-it 24/7, Mister Quick, etc.
     */
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $brand = null;

    /*
     * 4.25x6, 6x4.25, 8.5x11, etc.
     */
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $size = null;

    /*
     * direct_mail, eddm
     */
    #[ORM\Column(type: Types::TEXT)]
    private ?string $distributionMethod = null;

    /*
     * prospects, customers, prospects_and_customers
     */
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $targetAudience = null;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => true])]
    private bool $isActive = true;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): static
    {
        if (
            in_array($type, static::CAMPAIGN_PRODUCT_TYPES, true)
        ) {
            $this->type = $type;
        }

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getCategory(): ?string
    {
        return $this->category;
    }

    public function setCategory(?string $category): static
    {
        if (
            in_array($category, static::CAMPAIGN_PRODUCT_CATEGORIES, true)
        ) {
            $this->category = $category;
        }

        return $this;
    }

    public function getSubCategory(): ?string
    {
        return $this->subCategory;
    }

    public function setSubCategory(?string $subCategory): static
    {
        if (
            in_array($subCategory, static::CAMPAIGN_PRODUCT_SUBCATEGORIES, true)
        ) {
            $this->subCategory = $subCategory;
        }

        return $this;
    }

    public function getFormat(): ?string
    {
        return $this->format;
    }

    public function setFormat(?string $format): static
    {
        $this->format = $format;

        return $this;
    }

    public function getProspectPrice(): ?string
    {
        return $this->prospectPrice;
    }

    public function setProspectPrice(?string $prospectPrice): static
    {
        $this->prospectPrice = $prospectPrice;

        return $this;
    }

    public function getCustomerPrice(): ?string
    {
        return $this->customerPrice;
    }

    public function setCustomerPrice(?string $customerPrice): static
    {
        $this->customerPrice = $customerPrice;

        return $this;
    }

    public function getMailerDescription(): ?string
    {
        return $this->mailerDescription;
    }

    public function setMailerDescription(string $mailerDescription): static
    {
        $this->mailerDescription = $mailerDescription;

        return $this;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): static
    {
        if (
            in_array($code, static::CAMPAIGN_PRODUCT_CODES, true)
        ) {
            $this->code = $code;
        }

        return $this;
    }

    public function hasColoredStock(): ?bool
    {
        return $this->hasColoredStock;
    }

    public function setHasColoredStock(bool $hasColoredStock): static
    {
        $this->hasColoredStock = $hasColoredStock;

        return $this;
    }

    public function getBrand(): ?string
    {
        return $this->brand;
    }

    public function setBrand(?string $brand): static
    {
        $this->brand = $brand;

        return $this;
    }

    public function getSize(): ?string
    {
        return $this->size;
    }

    public function setSize(?string $size): static
    {
        $this->size = $size;

        return $this;
    }

    public function getDistributionMethod(): ?string
    {
        return $this->distributionMethod;
    }

    public function setDistributionMethod(string $distributionMethod): static
    {
        if (
            in_array($distributionMethod, static::CAMPAIGN_PRODUCT_DISTRIBUTION_METHODS, true)
        ) {
            $this->distributionMethod = $distributionMethod;
        }

        return $this;
    }

    public function getTargetAudience(): ?string
    {
        return $this->targetAudience;
    }

    public function setTargetAudience(?string $targetAudience): static
    {
        if (
            in_array($targetAudience, static::CAMPAIGN_PRODUCT_TARGET_AUDIENCES, true)
        ) {
            $this->targetAudience = $targetAudience;
        }

        return $this;
    }

    public function isActive(): ?bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): static
    {
        $this->isActive = $isActive;

        return $this;
    }
}
