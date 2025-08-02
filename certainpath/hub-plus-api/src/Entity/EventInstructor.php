<?php

namespace App\Entity;

use App\Repository\EventInstructorRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EventInstructorRepository::class)]
#[ORM\UniqueConstraint(fields: ['email'])]
class EventInstructor
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    /**
     * @var Collection<int, EventSession>
     */
    #[ORM\OneToMany(targetEntity: EventSession::class, mappedBy: 'instructor')]
    private Collection $eventSessions;

    #[ORM\Column(length: 255)]
    private ?string $email = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $phone = null;

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
            $eventSession->setInstructor($this);
        }

        return $this;
    }

    public function removeEventSession(EventSession $eventSession): static
    {
        if ($this->eventSessions->removeElement($eventSession)) {
            // set the owning side to null (unless already changed)
            if ($eventSession->getInstructor() === $this) {
                $eventSession->setInstructor(null);
            }
        }

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): static
    {
        $this->phone = $phone;

        return $this;
    }
}
