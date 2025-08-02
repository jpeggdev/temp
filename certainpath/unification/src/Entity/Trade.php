<?php

namespace App\Entity;

use App\Repository\TradeRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TradeRepository::class)]
class Trade
{
    public const ELECTRICAL = 'electrical';
    public const ELECTRICAL_CODE = 'ELEC';
    public const PLUMBING = 'plumbing';
    public const PLUMBING_CODE = 'PLBG';
    public const HVAC = 'hvac';
    public const HVAC_CODE = 'HVAC';
    public const ROOFING = 'roofing';
    public const ROOFING_CODE = 'ROOF';

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT, unique: true)]
    private string $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public static function roofing(): self
    {
        return new self(
            self::ROOFING
        );
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public static function electrical(): self
    {
        return new self(
            self::ELECTRICAL
        );
    }

    public static function plumbing(): self
    {
        return new self(
            self::PLUMBING
        );
    }

    public static function hvac(): self
    {
        return new self(
            self::HVAC
        );
    }

    public function equals(Trade $tradeToCompare): bool
    {
        return $this->name === $tradeToCompare->name;
    }

    public function isElectrical(): bool
    {
        return $this->name === self::ELECTRICAL;
    }

    public function isHvac(): bool
    {
        return $this->name === self::HVAC;
    }

    public function isRoofing(): bool
    {
        return $this->name === self::ROOFING;
    }

    public function isPlumbing(): bool
    {
        return $this->name === self::PLUMBING;
    }
}
