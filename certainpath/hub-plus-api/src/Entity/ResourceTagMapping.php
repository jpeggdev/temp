<?php

namespace App\Entity;

use App\Repository\ResourceTagMappingRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ResourceTagMappingRepository::class)]
class ResourceTagMapping
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'resourceTagMappings')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Resource $resource = null;

    #[ORM\ManyToOne(inversedBy: 'resourceTagMappings')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?ResourceTag $resourceTag = null;

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

    public function getResourceTag(): ?ResourceTag
    {
        return $this->resourceTag;
    }

    public function setResourceTag(?ResourceTag $resourceTag): static
    {
        $this->resourceTag = $resourceTag;

        return $this;
    }
}
