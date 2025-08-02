<?php

namespace App\Entity;

use App\Entity\Trait\TimestampableDateTimeTZTrait;
use App\Repository\EventDiscountRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\HasLifecycleCallbacks]
#[ORM\UniqueConstraint(fields: ['code'])]
#[ORM\Entity(repositoryClass: EventDiscountRepository::class)]
class EventDiscount
{
    use TimestampableDateTimeTZTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $code = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?DiscountType $discountType = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $discountValue = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $minimumPurchaseAmount = null;

    #[ORM\Column(nullable: true)]
    private ?int $maximumUses = null;

    #[ORM\Column(type: 'boolean', nullable: false, options: ['default' => true])]
    private bool $isActive = true;

    #[ORM\Column(type: Types::DATETIMETZ_IMMUTABLE, nullable: true)]
    private ?\DateTimeInterface $startDate = null;

    #[ORM\Column(type: Types::DATETIMETZ_IMMUTABLE, nullable: true)]
    private ?\DateTimeInterface $endDate = null;

    /**
     * @var Collection<int, EventEventDiscount>
     */
    #[ORM\OneToMany(targetEntity: EventEventDiscount::class, mappedBy: 'eventDiscount')]
    private Collection $eventEventDiscounts;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $deletedAt = null;

    public function __construct()
    {
        $this->eventEventDiscounts = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): static
    {
        $this->code = $code;

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

    public function getDiscountType(): ?DiscountType
    {
        return $this->discountType;
    }

    public function setDiscountType(?DiscountType $discountType): static
    {
        $this->discountType = $discountType;

        return $this;
    }

    public function getDiscountValue(): ?string
    {
        return $this->discountValue;
    }

    public function setDiscountValue(string $discountValue): static
    {
        $this->discountValue = $discountValue;

        return $this;
    }

    public function getMinimumPurchaseAmount(): ?string
    {
        return $this->minimumPurchaseAmount;
    }

    public function setMinimumPurchaseAmount(?string $minimumPurchaseAmount): static
    {
        $this->minimumPurchaseAmount = $minimumPurchaseAmount;

        return $this;
    }

    public function getMaximumUses(): ?int
    {
        return $this->maximumUses;
    }

    public function setMaximumUses(?int $maximumUses): static
    {
        $this->maximumUses = $maximumUses;

        return $this;
    }

    public function getStartDate(): ?\DateTimeInterface
    {
        return $this->startDate;
    }

    public function setStartDate(?\DateTimeInterface $startDate): static
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getEndDate(): ?\DateTimeInterface
    {
        return $this->endDate;
    }

    public function setEndDate(?\DateTimeInterface $endDate): static
    {
        $this->endDate = $endDate;

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

    /**
     * @return Collection<int, Event>
     */
    public function getEvents(): Collection
    {
        $events = new ArrayCollection();

        foreach ($this->eventEventDiscounts as $eventEventDiscount) {
            $events->add($eventEventDiscount->getEvent());
        }

        return $events;
    }

    /**
     * @return Collection<int, EventEventDiscount>
     */
    public function getEventEventDiscounts(): Collection
    {
        return $this->eventEventDiscounts;
    }

    public function addEventEventDiscount(
        EventEventDiscount $eventEventDiscount,
    ): static {
        if (!$this->eventEventDiscounts->contains($eventEventDiscount)) {
            $this->eventEventDiscounts->add($eventEventDiscount);
            $eventEventDiscount->setEventDiscount($this);
        }

        return $this;
    }

    public function removeEventEventDiscount(
        EventEventDiscount $eventEventDiscount,
    ): static {
        if (
            $this->eventEventDiscounts->removeElement($eventEventDiscount)
            && $eventEventDiscount->getEventDiscount() === $this
        ) {
            $eventEventDiscount->setEventDiscount(null);
        }

        return $this;
    }

    public function getDeletedAt(): ?\DateTimeImmutable
    {
        return $this->deletedAt;
    }

    public function setDeletedAt(?\DateTimeImmutable $deletedAt): static
    {
        $this->deletedAt = $deletedAt;

        return $this;
    }
}
