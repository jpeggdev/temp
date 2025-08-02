<?php

declare(strict_types=1);

namespace App\Entity;

use App\Doctrine\Types\TsVectorType;
use App\Entity\Trait\TimestampableDateTimeTZTrait;
use App\Entity\Trait\UuidTrait;
use App\Repository\EventRepository\EventRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EventRepository::class)]
#[ORM\Table(name: 'event')]
#[ORM\UniqueConstraint(
    fields: ['eventCode']
)]
#[ORM\UniqueConstraint(fields: ['uuid'])]
#[ORM\HasLifecycleCallbacks]
class Event
{
    use TimestampableDateTimeTZTrait;
    use UuidTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATETIMETZ_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $deletedAt = null;

    #[ORM\Column(length: 100)]
    private string $eventCode;

    #[ORM\Column(length: 255)]
    private string $eventName;

    #[ORM\Column(type: 'text')]
    private string $eventDescription;

    #[ORM\Column(type: 'decimal', precision: 18, scale: 2)]
    private float $eventPrice;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private ?bool $isPublished = false;

    #[ORM\ManyToOne(targetEntity: EventType::class)]
    #[ORM\JoinColumn(name: 'event_type_id', referencedColumnName: 'id')]
    private ?EventType $eventType = null;

    #[ORM\ManyToOne(targetEntity: EventCategory::class, inversedBy: 'events')]
    #[ORM\JoinColumn(name: 'event_category_id', referencedColumnName: 'id')]
    private ?EventCategory $eventCategory = null;

    /**
     * @var Collection<int, EventFile>
     */
    #[ORM\OneToMany(targetEntity: EventFile::class, mappedBy: 'event')]
    private Collection $eventFiles;

    /**
     * @var Collection<int, EventSession>
     */
    #[ORM\OneToMany(targetEntity: EventSession::class, mappedBy: 'event')]
    private Collection $eventSessions;

    /**
     * @var Collection<int, EventTagMapping>
     */
    #[ORM\OneToMany(targetEntity: EventTagMapping::class, mappedBy: 'event')]
    private Collection $eventTagMappings;

    /**
     * @var Collection<int, EventTradeMapping>
     */
    #[ORM\OneToMany(targetEntity: EventTradeMapping::class, mappedBy: 'event')]
    private Collection $eventTradeMappings;

    /**
     * @var Collection<int, EventEmployeeRoleMapping>
     */
    #[ORM\OneToMany(targetEntity: EventEmployeeRoleMapping::class, mappedBy: 'event')]
    private Collection $eventEmployeeRoleMappings;

    #[ORM\Column(type: TsVectorType::NAME, nullable: true)]
    private ?string $searchVector = null;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private int $viewCount = 0;

    /**
     * @var Collection<int, EventFavorite>
     */
    #[ORM\OneToMany(targetEntity: EventFavorite::class, mappedBy: 'event')]
    private Collection $eventFavorites;

    /**
     * @var Collection<int, EventEventDiscount>
     */
    #[ORM\OneToMany(
        targetEntity: EventEventDiscount::class,
        mappedBy: 'event',
        orphanRemoval: true
    )]
    private Collection $eventEventDiscounts;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private ?bool $isVoucherEligible = false;

    #[ORM\ManyToOne]
    private ?File $thumbnail = null;

    public function __construct()
    {
        $this->eventFiles = new ArrayCollection();
        $this->eventSessions = new ArrayCollection();
        $this->eventTagMappings = new ArrayCollection();
        $this->eventTradeMappings = new ArrayCollection();
        $this->eventEmployeeRoleMappings = new ArrayCollection();
        $this->eventFavorites = new ArrayCollection();
        $this->eventEventDiscounts = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEventCode(): string
    {
        return $this->eventCode;
    }

    public function setEventCode(string $eventCode): self
    {
        $this->eventCode = $eventCode;

        return $this;
    }

    public function getEventName(): string
    {
        return $this->eventName;
    }

    public function setEventName(string $eventName): self
    {
        $this->eventName = $eventName;

        return $this;
    }

    public function getEventDescription(): string
    {
        return $this->eventDescription;
    }

    public function setEventDescription(string $eventDescription): self
    {
        $this->eventDescription = $eventDescription;

        return $this;
    }

    public function getEventPrice(): float
    {
        return $this->eventPrice;
    }

    public function setEventPrice(?float $eventPrice): self
    {
        $this->eventPrice = $eventPrice;

        return $this;
    }

    public function getIsPublished(): ?bool
    {
        return $this->isPublished;
    }

    public function setIsPublished(?bool $isPublished): self
    {
        $this->isPublished = $isPublished;

        return $this;
    }

    public function getDeletedAt(): ?\DateTimeImmutable
    {
        return $this->deletedAt;
    }

    public function setDeletedAt(?\DateTimeImmutable $deletedAt): self
    {
        $this->deletedAt = $deletedAt;

        return $this;
    }

    public function isDeleted(): bool
    {
        return null !== $this->deletedAt;
    }

    public function getEventType(): ?EventType
    {
        return $this->eventType;
    }

    public function getEventTypeName(): ?string
    {
        return $this->eventType?->getName();
    }

    public function setEventType(?EventType $eventType): self
    {
        $this->eventType = $eventType;

        return $this;
    }

    public function getEventCategory(): ?EventCategory
    {
        return $this->eventCategory;
    }

    public function setEventCategory(?EventCategory $eventCategory): self
    {
        $this->eventCategory = $eventCategory;

        return $this;
    }

    public function getEventCategoryName(): ?string
    {
        return $this->eventCategory?->getName();
    }

    /**
     * @return Collection<int, EventFile>
     */
    public function getEventFiles(): Collection
    {
        return $this->eventFiles;
    }

    public function addEventFile(EventFile $eventFile): self
    {
        if (!$this->eventFiles->contains($eventFile)) {
            $this->eventFiles->add($eventFile);
            $eventFile->setEvent($this);
        }

        return $this;
    }

    public function removeEventFile(EventFile $eventFile): self
    {
        if ($this->eventFiles->removeElement($eventFile)) {
            // Set the owning side to null if necessary
            if ($eventFile->getEvent() === $this) {
                $eventFile->setEvent(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, EventSession>
     */
    public function getEventSessions(): Collection
    {
        return $this->eventSessions;
    }

    public function addEventSession(EventSession $eventSession): self
    {
        if (!$this->eventSessions->contains($eventSession)) {
            $this->eventSessions->add($eventSession);
            $eventSession->setEvent($this);
        }

        return $this;
    }

    public function removeEventSession(EventSession $eventSession): self
    {
        if ($this->eventSessions->removeElement($eventSession)) {
            // Set the owning side to null if necessary
            if ($eventSession->getEvent() === $this) {
                $eventSession->setEvent(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, EventTagMapping>
     */
    public function getEventTagMappings(): Collection
    {
        return $this->eventTagMappings;
    }

    public function addEventTagMapping(EventTagMapping $eventTagMapping): static
    {
        if (!$this->eventTagMappings->contains($eventTagMapping)) {
            $this->eventTagMappings->add($eventTagMapping);
            $eventTagMapping->setEvent($this);
        }

        return $this;
    }

    public function removeEventTagMapping(EventTagMapping $eventTagMapping): static
    {
        if ($this->eventTagMappings->removeElement($eventTagMapping)) {
            // set the owning side to null (unless already changed)
            if ($eventTagMapping->getEvent() === $this) {
                $eventTagMapping->setEvent(null);
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
            $eventTradeMapping->setEvent($this);
        }

        return $this;
    }

    public function removeEventTradeMapping(EventTradeMapping $eventTradeMapping): static
    {
        if ($this->eventTradeMappings->removeElement($eventTradeMapping)) {
            // set the owning side to null (unless already changed)
            if ($eventTradeMapping->getEvent() === $this) {
                $eventTradeMapping->setEvent(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, EventEmployeeRoleMapping>
     */
    public function getEventEmployeeRoleMappings(): Collection
    {
        return $this->eventEmployeeRoleMappings;
    }

    public function addEventEmployeeRoleMapping(EventEmployeeRoleMapping $eventEmployeeRoleMapping): static
    {
        if (!$this->eventEmployeeRoleMappings->contains($eventEmployeeRoleMapping)) {
            $this->eventEmployeeRoleMappings->add($eventEmployeeRoleMapping);
            $eventEmployeeRoleMapping->setEvent($this);
        }

        return $this;
    }

    public function removeEventEmployeeRoleMapping(EventEmployeeRoleMapping $eventEmployeeRoleMapping): static
    {
        if ($this->eventEmployeeRoleMappings->removeElement($eventEmployeeRoleMapping)) {
            // set the owning side to null (unless already changed)
            if ($eventEmployeeRoleMapping->getEvent() === $this) {
                $eventEmployeeRoleMapping->setEvent(null);
            }
        }

        return $this;
    }

    public function getSearchVector(): ?string
    {
        return $this->searchVector;
    }

    public function setSearchVector(?string $searchVector): void
    {
        $this->searchVector = $searchVector;
    }

    public function getViewCount(): ?int
    {
        return $this->viewCount;
    }

    public function setViewCount(?int $viewCount): void
    {
        $this->viewCount = $viewCount;
    }

    /**
     * @return Collection<int, EventFavorite>
     */
    public function getEventFavorites(): Collection
    {
        return $this->eventFavorites;
    }

    public function addEventFavorite(EventFavorite $eventFavorite): static
    {
        if (!$this->eventFavorites->contains($eventFavorite)) {
            $this->eventFavorites->add($eventFavorite);
            $eventFavorite->setEvent($this);
        }

        return $this;
    }

    public function removeEventFavorite(EventFavorite $eventFavorite): static
    {
        if ($this->eventFavorites->removeElement($eventFavorite)) {
            // set the owning side to null (unless already changed)
            if ($eventFavorite->getEvent() === $this) {
                $eventFavorite->setEvent(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, EventEventDiscount>
     */
    public function getEventEventDiscounts(): Collection
    {
        return $this->eventEventDiscounts;
    }

    public function addEventEventDiscount(
        EventEventDiscount $eventEventDiscountCode,
    ): static {
        if (!$this->eventEventDiscounts->contains($eventEventDiscountCode)) {
            $this->eventEventDiscounts->add($eventEventDiscountCode);
            $eventEventDiscountCode->setEvent($this);
        }

        return $this;
    }

    public function removeEventEventDiscount(
        EventEventDiscount $eventEventDiscount,
    ): static {
        if (
            $this->eventEventDiscounts->removeElement($eventEventDiscount)
            && $eventEventDiscount->getEvent() === $this
        ) {
            $eventEventDiscount->setEvent(null);
        }

        return $this;
    }

    public function isVoucherEligible(): ?bool
    {
        return $this->isVoucherEligible;
    }

    public function setIsVoucherEligible(bool $isVoucherEligible): static
    {
        $this->isVoucherEligible = $isVoucherEligible;

        return $this;
    }

    public function getThumbnail(): ?File
    {
        return $this->thumbnail;
    }

    public function setThumbnail(?File $thumbnail): static
    {
        $this->thumbnail = $thumbnail;

        return $this;
    }
}
