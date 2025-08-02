<?php

namespace App\Entity;

use App\Entity\Trait\TimestampableDateTimeTZTrait;
use App\Repository\ResourceRelationRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ResourceRelationRepository::class)]
#[ORM\HasLifecycleCallbacks]
class ResourceRelation
{
    use TimestampableDateTimeTZTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'resourceRelations')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Resource $resource = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'related_resource_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private ?Resource $relatedResource = null;

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

    public function getRelatedResource(): ?Resource
    {
        return $this->relatedResource;
    }

    public function setRelatedResource(?Resource $relatedResource): static
    {
        $this->relatedResource = $relatedResource;

        return $this;
    }
}
