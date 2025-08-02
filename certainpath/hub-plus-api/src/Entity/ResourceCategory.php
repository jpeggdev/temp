<?php

namespace App\Entity;

use App\Repository\ResourceCategoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ResourceCategoryRepository::class)]
#[ORM\UniqueConstraint(fields: ['name'])]
class ResourceCategory
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    /**
     * @var Collection<int, ResourceCategoryMapping>
     */
    #[ORM\OneToMany(targetEntity: ResourceCategoryMapping::class, mappedBy: 'resourceCategory')]
    private Collection $resourceCategoryMappings;

    public function __construct()
    {
        $this->resourceCategoryMappings = new ArrayCollection();
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
     * @return Collection<int, ResourceCategoryMapping>
     */
    public function getResourceCategoryMappings(): Collection
    {
        return $this->resourceCategoryMappings;
    }

    public function addResourceCategoryMapping(ResourceCategoryMapping $resourceCategoryMapping): static
    {
        if (!$this->resourceCategoryMappings->contains($resourceCategoryMapping)) {
            $this->resourceCategoryMappings->add($resourceCategoryMapping);
            $resourceCategoryMapping->setResourceCategory($this);
        }

        return $this;
    }

    public function removeResourceCategoryMapping(ResourceCategoryMapping $resourceCategoryMapping): static
    {
        if ($this->resourceCategoryMappings->removeElement($resourceCategoryMapping)) {
            // set the owning side to null (unless already changed)
            if ($resourceCategoryMapping->getResourceCategory() === $this) {
                $resourceCategoryMapping->setResourceCategory(null);
            }
        }

        return $this;
    }
}
