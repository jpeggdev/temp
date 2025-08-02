<?php

namespace App\Entity;

use App\Repository\ResourceTagRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ResourceTagRepository::class)]
#[ORM\UniqueConstraint(fields: ['name'])]
class ResourceTag
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    /**
     * @var Collection<int, ResourceTagMapping>
     */
    #[ORM\OneToMany(targetEntity: ResourceTagMapping::class, mappedBy: 'resourceTag')]
    private Collection $resourceTagMappings;

    public function __construct()
    {
        $this->resourceTagMappings = new ArrayCollection();
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
     * @return Collection<int, ResourceTagMapping>
     */
    public function getResourceTagMappings(): Collection
    {
        return $this->resourceTagMappings;
    }

    public function addResourceTagMapping(ResourceTagMapping $resourceTagMapping): static
    {
        if (!$this->resourceTagMappings->contains($resourceTagMapping)) {
            $this->resourceTagMappings->add($resourceTagMapping);
            $resourceTagMapping->setResourceTag($this);
        }

        return $this;
    }

    public function removeResourceTagMapping(ResourceTagMapping $resourceTagMapping): static
    {
        if ($this->resourceTagMappings->removeElement($resourceTagMapping)) {
            // set the owning side to null (unless already changed)
            if ($resourceTagMapping->getResourceTag() === $this) {
                $resourceTagMapping->setResourceTag(null);
            }
        }

        return $this;
    }
}
