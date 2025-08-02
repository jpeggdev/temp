<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Trait\TimestampableDateTimeTZTrait;
use App\Entity\Trait\UuidTrait;
use App\Repository\EventSession\EventSessionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EventSessionRepository::class)]
#[ORM\Table(name: 'event_session')]
#[ORM\UniqueConstraint(fields: ['uuid'])]
#[ORM\HasLifecycleCallbacks]
class EventSession
{
    use TimestampableDateTimeTZTrait;
    use UuidTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Event::class, inversedBy: 'eventSessions')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Event $event = null;

    #[ORM\Column(type: Types::DATETIMETZ_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $startDate = null;

    #[ORM\Column(type: Types::DATETIMETZ_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $endDate = null;

    #[ORM\Column(type: 'integer')]
    private int $maxEnrollments = 0;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $virtualLink = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $notes = null;
    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private ?bool $isPublished = false;

    #[ORM\ManyToOne(inversedBy: 'eventSessions')]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?EventInstructor $instructor = null;

    /**
     * @var Collection<int, EventCheckout>
     */
    #[ORM\OneToMany(targetEntity: EventCheckout::class, mappedBy: 'eventSession')]
    private Collection $eventCheckouts;

    /**
     * @var Collection<int, EventEnrollment>
     */
    #[ORM\OneToMany(targetEntity: EventEnrollment::class, mappedBy: 'eventSession')]
    private Collection $eventEnrollments;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\ManyToOne(inversedBy: 'eventSessions')]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?EventVenue $venue = null;

    #[ORM\ManyToOne(inversedBy: 'eventSessions')]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?Timezone $timezone = null;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private ?bool $isVirtualOnly = false;

    /**
     * @var Collection<int, EventEnrollmentWaitlist>
     */
    #[ORM\OneToMany(targetEntity: EventEnrollmentWaitlist::class, mappedBy: 'eventSession')]
    private Collection $eventEnrollmentWaitlists;

    /**
     * @var Collection<int, Invoice>
     */
    #[ORM\OneToMany(targetEntity: Invoice::class, mappedBy: 'eventSession')]
    private Collection $invoices;

    public function __construct()
    {
        $this->eventCheckouts = new ArrayCollection();
        $this->eventEnrollments = new ArrayCollection();
        $this->eventEnrollmentWaitlists = new ArrayCollection();
        $this->invoices = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEvent(): ?Event
    {
        return $this->event;
    }

    public function setEvent(?Event $event): self
    {
        $this->event = $event;

        return $this;
    }

    public function getStartDate(): ?\DateTimeImmutable
    {
        return $this->startDate;
    }

    public function setStartDate(?\DateTimeImmutable $startDate): self
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getEndDate(): ?\DateTimeImmutable
    {
        return $this->endDate;
    }

    public function setEndDate(?\DateTimeImmutable $endDate): self
    {
        $this->endDate = $endDate;

        return $this;
    }

    public function getMaxEnrollments(): int
    {
        return $this->maxEnrollments;
    }

    public function setMaxEnrollments(int $maxEnrollments): self
    {
        $this->maxEnrollments = $maxEnrollments;

        return $this;
    }

    public function getVirtualLink(): ?string
    {
        return $this->virtualLink;
    }

    public function setVirtualLink(?string $virtualLink): self
    {
        $this->virtualLink = $virtualLink;

        return $this;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): self
    {
        $this->notes = $notes;

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

    public function getInstructor(): ?EventInstructor
    {
        return $this->instructor;
    }

    public function setInstructor(?EventInstructor $instructor): static
    {
        $this->instructor = $instructor;

        return $this;
    }

    /**
     * @return Collection<int, EventCheckout>
     */
    public function getEventCheckouts(): Collection
    {
        return $this->eventCheckouts;
    }

    public function addEventCheckout(EventCheckout $eventCheckout): static
    {
        if (!$this->eventCheckouts->contains($eventCheckout)) {
            $this->eventCheckouts->add($eventCheckout);
            $eventCheckout->setEventSession($this);
        }

        return $this;
    }

    public function removeEventCheckout(EventCheckout $eventCheckout): static
    {
        if ($this->eventCheckouts->removeElement($eventCheckout)) {
            // set the owning side to null (unless already changed)
            if ($eventCheckout->getEventSession() === $this) {
                $eventCheckout->setEventSession(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, EventEnrollment>
     */
    public function getEventEnrollments(): Collection
    {
        return $this->eventEnrollments;
    }

    public function addEventEnrollment(EventEnrollment $eventEnrollment): static
    {
        if (!$this->eventEnrollments->contains($eventEnrollment)) {
            $this->eventEnrollments->add($eventEnrollment);
            $eventEnrollment->setEventSession($this);
        }

        return $this;
    }

    public function removeEventEnrollment(EventEnrollment $eventEnrollment): static
    {
        if ($this->eventEnrollments->removeElement($eventEnrollment)) {
            // set the owning side to null (unless already changed)
            if ($eventEnrollment->getEventSession() === $this) {
                $eventEnrollment->setEventSession(null);
            }
        }

        return $this;
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

    public function getVenue(): ?EventVenue
    {
        return $this->venue;
    }

    public function setVenue(?EventVenue $venue): static
    {
        $this->venue = $venue;

        return $this;
    }

    public function getTimezone(): ?Timezone
    {
        return $this->timezone;
    }

    public function setTimezone(?Timezone $timezone): static
    {
        $this->timezone = $timezone;

        return $this;
    }

    public function isVirtualOnly(): ?bool
    {
        return $this->isVirtualOnly;
    }

    public function setIsVirtualOnly(bool $isVirtualOnly): static
    {
        $this->isVirtualOnly = $isVirtualOnly;

        return $this;
    }

    /**
     * @return Collection<int, EventEnrollmentWaitlist>
     */
    public function getEventEnrollmentWaitlists(): Collection
    {
        return $this->eventEnrollmentWaitlists;
    }

    public function addEventEnrollmentWaitlist(EventEnrollmentWaitlist $eventEnrollmentWaitlist): static
    {
        if (!$this->eventEnrollmentWaitlists->contains($eventEnrollmentWaitlist)) {
            $this->eventEnrollmentWaitlists->add($eventEnrollmentWaitlist);
            $eventEnrollmentWaitlist->setEventSession($this);
        }

        return $this;
    }

    public function removeEventEnrollmentWaitlist(EventEnrollmentWaitlist $eventEnrollmentWaitlist): static
    {
        if ($this->eventEnrollmentWaitlists->removeElement($eventEnrollmentWaitlist)) {
            // set the owning side to null (unless already changed)
            if ($eventEnrollmentWaitlist->getEventSession() === $this) {
                $eventEnrollmentWaitlist->setEventSession(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Invoice>
     */
    public function getInvoices(): Collection
    {
        return $this->invoices;
    }

    public function addInvoice(Invoice $invoice): static
    {
        if (!$this->invoices->contains($invoice)) {
            $this->invoices->add($invoice);
            $invoice->setEventSession($this);
        }

        return $this;
    }

    public function removeInvoice(Invoice $invoice): static
    {
        if ($this->invoices->removeElement($invoice)) {
            // set the owning side to null (unless already changed)
            if ($invoice->getEventSession() === $this) {
                $invoice->setEventSession(null);
            }
        }

        return $this;
    }
}
