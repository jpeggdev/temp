<?php

namespace App\Entity;

use App\Repository\ResourceCategoryMappingRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ResourceCategoryMappingRepository::class)]
class ResourceCategoryMapping
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'resourceCategoryMappings')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Resource $resource = null;

    #[ORM\ManyToOne(inversedBy: 'resourceCategoryMappings')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?ResourceCategory $resourceCategory = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getResource(): ?Resource
    {
        return $this->resource;
    }

    public function setResource(?Resource $resource): static
    {
        $this->resource = $resource;

        return $this;
    }

    public function getResourceCategory(): ?ResourceCategory
    {
        return $this->resourceCategory;
    }

    public function setResourceCategory(?ResourceCategory $resourceCategory): static
    {
        $this->resourceCategory = $resourceCategory;

        return $this;
    }
}
