<?php

namespace App\Entity;

use App\Exception\UnsupportedTrade;
use App\Repository\TradeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TradeRepository::class)]
#[ORM\UniqueConstraint(fields: ['name'])]
class Trade
{
    public const string ELECTRICAL = 'electrical';
    public const string HVAC = 'hvac';
    public const string PLUMBING = 'plumbing';
    public const string ROOFING = 'roofing';
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    /**
     * @var Collection<int, CompanyTrade>
     */
    #[ORM\OneToMany(targetEntity: CompanyTrade::class, mappedBy: 'trade')]
    private Collection $companyTrades;

    #[ORM\Column(length: 255)]
    private ?string $description = null;
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $longName = null;

    /**
     * @var Collection<int, ResourceTradeMapping>
     */
    #[ORM\OneToMany(targetEntity: ResourceTradeMapping::class, mappedBy: 'trade')]
    private Collection $resourceTradeMappings;

    /**
     * @var Collection<int, EventTradeMapping>
     */
    #[ORM\OneToMany(targetEntity: EventTradeMapping::class, mappedBy: 'trade')]
    private Collection $eventTradeMappings;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $icon = null;

    public function __construct()
    {
        $this->companyTrades = new ArrayCollection();
        $this->resourceTradeMappings = new ArrayCollection();
        $this->eventTradeMappings = new ArrayCollection();
    }

    /**
     * @return Collection<int, CompanyTrade>
     */
    public function getCompanyTrades(): Collection
    {
        return $this->companyTrades;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    private function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    private function setDescription(string $description): void
    {
        $this->description = $description;
    }

    private function setLongName(string $longName): void
    {
        $this->longName = $longName;
    }

    public function getLongName(): ?string
    {
        return $this->longName;
    }

    // region Domain-Specific Methods
    public static function electrical(): self
    {
        $instance = new self();
        $instance->setName('ESI');
        $instance->setLongName(self::ELECTRICAL);
        $instance->setDescription(
            'Electrical Service Installer - Specializes in'
            .' electrical systems installation and maintenance'
        );

        return $instance;
    }

    public static function hvac(): self
    {
        $instance = new self();
        $instance->setName('ASI');
        $instance->setLongName(self::HVAC);
        $instance->setDescription(
            'HVAC Service Installer - Specializes in '
            .'installation and servicing of HVAC systems and related mechanical installations'
        );

        return $instance;
    }

    public static function plumbing(): self
    {
        $instance = new self();
        $instance->setName('PSI');
        $instance->setLongName(self::PLUMBING);
        $instance->setDescription(
            'Plumbing Service Installer - Focused on '
            .'installation and repair of plumbing systems'
        );

        return $instance;
    }

    public static function roofing(): Trade
    {
        $instance = new self();
        $instance->setName('RSI');
        $instance->setLongName(self::ROOFING);
        $instance->setDescription(
            'Roofing Service Installer - Specializes in'
            .' roofing installation and repair'
        );

        return $instance;
    }

    /**
     * @throws UnsupportedTrade
     */
    public static function fromLongName(string $longName): self
    {
        if (self::ELECTRICAL === $longName) {
            return self::electrical();
        }

        if (self::HVAC === $longName) {
            return self::hvac();
        }

        if (self::PLUMBING === $longName) {
            return self::plumbing();
        }

        if (self::ROOFING === $longName) {
            return self::roofing();
        }

        throw new UnsupportedTrade($longName);
    }

    public function is(Trade $tradeToCompare): bool
    {
        return $this->getId() === $tradeToCompare->getId();
    }

    public function updateFromReference(Trade $trade): void
    {
        $this->setName($trade->getName());
        $this->setDescription($trade->getDescription());
        $this->setLongName($trade->getLongName());
    }
    // endregion

    /**
     * @return Collection<int, ResourceTradeMapping>
     */
    public function getResourceTradeMappings(): Collection
    {
        return $this->resourceTradeMappings;
    }

    public function addResourceTradeMapping(ResourceTradeMapping $resourceTradeMapping): static
    {
        if (!$this->resourceTradeMappings->contains($resourceTradeMapping)) {
            $this->resourceTradeMappings->add($resourceTradeMapping);
            $resourceTradeMapping->setTrade($this);
        }

        return $this;
    }

    public function removeResourceTradeMapping(ResourceTradeMapping $resourceTradeMapping): static
    {
        if ($this->resourceTradeMappings->removeElement($resourceTradeMapping)) {
            // set the owning side to null (unless already changed)
            if ($resourceTradeMapping->getTrade() === $this) {
                $resourceTradeMapping->setTrade(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, EventTradeMapping>
     */
    public function getEventTradeMappings(): Collection
    {
        return $this->eventTradeMappings;
    }

    public function addEventTradeMapping(EventTradeMapping $eventTradeMapping): static
    {
        if (!$this->eventTradeMappings->contains($eventTradeMapping)) {
            $this->eventTradeMappings->add($eventTradeMapping);
            $eventTradeMapping->setTrade($this);
        }

        return $this;
    }

    public function removeEventTradeMapping(EventTradeMapping $eventTradeMapping): static
    {
        if ($this->eventTradeMappings->removeElement($eventTradeMapping)) {
            // set the owning side to null (unless already changed)
            if ($eventTradeMapping->getTrade() === $this) {
                $eventTradeMapping->setTrade(null);
            }
        }

        return $this;
    }

    public function getIcon(): ?string
    {
        return $this->icon;
    }

    public function setIcon(?string $icon): static
    {
        $this->icon = $icon;

        return $this;
    }
}
