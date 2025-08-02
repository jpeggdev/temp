<?php

namespace App\Entity;

use App\Repository\EventTagRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EventTagRepository::class)]
#[ORM\UniqueConstraint(fields: ['name'])]
class EventTag
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    /**
     * @var Collection<int, EventTagMapping>
     */
    #[ORM\OneToMany(targetEntity: EventTagMapping::class, mappedBy: 'eventTag')]
    private Collection $eventTagMappings;

    public function __construct()
    {
        $this->eventTagMappings = new ArrayCollection();
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
            $eventTagMapping->setEventTag($this);
        }

        return $this;
    }

    public function removeEventTagMapping(EventTagMapping $eventTagMapping): static
    {
        if ($this->eventTagMappings->removeElement($eventTagMapping)) {
            // set the owning side to null (unless already changed)
            if ($eventTagMapping->getEventTag() === $this) {
                $eventTagMapping->setEventTag(null);
            }
        }

        return $this;
    }
}
