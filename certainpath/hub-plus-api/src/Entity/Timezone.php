<?php

namespace App\Entity;

use App\Repository\TimezoneRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\UniqueConstraint(fields: ['name'])]
#[ORM\Entity(repositoryClass: TimezoneRepository::class)]
class Timezone
{
    public const string TIMEZONE_ET_NAME = 'Eastern Time (ET)';
    public const string TIMEZONE_CT_NAME = 'Central Time (CT)';
    public const string TIMEZONE_MT_NAME = 'Mountain Time (MT)';
    public const string TIMEZONE_PT_NAME = 'Pacific Time (PT)';
    public const string TIMEZONE_AKT_NAME = 'Alaska Time (AKT)';
    public const string TIMEZONE_HAT_NAME = 'Hawaii-Aleutian Time (HAT)';

    public const string TIMEZONE_ET_SHORT_NAME = 'ET';
    public const string TIMEZONE_CT_SHORT_NAME = 'CT';
    public const string TIMEZONE_MT_SHORT_NAME = 'MT';
    public const string TIMEZONE_PT_SHORT_NAME = 'PT';
    public const string TIMEZONE_AKT_SHORT_NAME = 'AKT';
    public const string TIMEZONE_HAT_SHORT_NAME = 'HAT';

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $shortName = null;

    /**
     * @var Collection<int, EventSession>
     */
    #[ORM\OneToMany(targetEntity: EventSession::class, mappedBy: 'timezone')]
    private Collection $eventSessions;

    #[ORM\Column(length: 255)]
    private ?string $identifier = null;

    public function __construct()
    {
        $this->eventSessions = new ArrayCollection();
    }

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

    public function getShortName(): ?string
    {
        return $this->shortName;
    }

    public function setShortName(string $shortName): static
    {
        $this->shortName = $shortName;

        return $this;
    }

    /**
     * @return Collection<int, EventSession>
     */
    public function getEventSessions(): Collection
    {
        return $this->eventSessions;
    }

    public function addEventSession(EventSession $eventSession): static
    {
        if (!$this->eventSessions->contains($eventSession)) {
            $this->eventSessions->add($eventSession);
            $eventSession->setTimezone($this);
        }

        return $this;
    }

    public function removeEventSession(EventSession $eventSession): static
    {
        if ($this->eventSessions->removeElement($eventSession)) {
            // set the owning side to null (unless already changed)
            if ($eventSession->getTimezone() === $this) {
                $eventSession->setTimezone(null);
            }
        }

        return $this;
    }

    public function getIdentifier(): ?string
    {
        return $this->identifier;
    }

    public function setIdentifier(string $identifier): static
    {
        $this->identifier = $identifier;

        return $this;
    }
}
