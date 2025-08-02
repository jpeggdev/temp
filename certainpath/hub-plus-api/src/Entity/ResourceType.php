<?php

namespace App\Entity;

use App\Repository\ResourceTypeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ResourceTypeRepository::class)]
#[ORM\UniqueConstraint(fields: ['name'])]
#[ORM\UniqueConstraint(name: 'unique_resource_type_is_default', columns: ['is_default'], options: ['where' => '(is_default = true)'])]
class ResourceType
{
    public const RESOURCE_TYPE_DOCUMENT = 'Document';
    public const RESOURCE_TYPE_VIDEO = 'Video';
    public const RESOURCE_TYPE_PODCAST = 'Podcast';

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    /**
     * @var Collection<int, \App\Entity\Resource>
     */
    #[ORM\OneToMany(targetEntity: Resource::class, mappedBy: 'type')]
    private Collection $resources;

    #[ORM\Column(nullable: false, options: ['default' => 'false'])]
    private ?bool $requiresContentUrl = false;

    #[ORM\Column(nullable: false, options: ['default' => 'false'])]
    private ?bool $isDefault = false;

    #[ORM\Column(nullable: false, options: ['default' => 0])]
    private ?int $sortOrder = 0;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $icon = null;
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $primaryIcon = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $backgroundColor = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $textColor = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $borderColor = null;

    public function __construct()
    {
        $this->resources = new ArrayCollection();
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
     * @return Collection<int, \App\Entity\Resource>
     */
    public function getResources(): Collection
    {
        return $this->resources;
    }

    public function addResource(Resource $resource): static
    {
        if (!$this->resources->contains($resource)) {
            $this->resources->add($resource);
            $resource->setType($this);
        }

        return $this;
    }

    public function removeResource(Resource $resource): static
    {
        if ($this->resources->removeElement($resource)) {
            // set the owning side to null (unless already changed)
            if ($resource->getType() === $this) {
                $resource->setType(null);
            }
        }

        return $this;
    }

    public function isRequiresContentUrl(): ?bool
    {
        return $this->requiresContentUrl;
    }

    public function setRequiresContentUrl(bool $requiresContentUrl): static
    {
        $this->requiresContentUrl = $requiresContentUrl;

        return $this;
    }

    public function isDefault(): ?bool
    {
        return $this->isDefault;
    }

    public function setIsDefault(bool $isDefault): static
    {
        $this->isDefault = $isDefault;

        return $this;
    }

    public function getSortOrder(): ?int
    {
        return $this->sortOrder;
    }

    public function setSortOrder(int $sortOrder): static
    {
        $this->sortOrder = $sortOrder;

        return $this;
    }

    public function getIcon(): ?string
    {
        return $this->icon;
    }

    public function setIcon(?string $icon): static
    {
        $this->icon = $icon;

        return $this;
    }

    public function getPrimaryIcon(): ?string
    {
        return $this->primaryIcon;
    }

    public function setPrimaryIcon(?string $primaryIcon): static
    {
        $this->primaryIcon = $primaryIcon;

        return $this;
    }

    public function getBackgroundColor(): ?string
    {
        return $this->backgroundColor;
    }

    public function setBackgroundColor(?string $backgroundColor): static
    {
        $this->backgroundColor = $backgroundColor;

        return $this;
    }

    public function getTextColor(): ?string
    {
        return $this->textColor;
    }

    public function setTextColor(?string $textColor): static
    {
        $this->textColor = $textColor;

        return $this;
    }

    public function getBorderColor(): ?string
    {
        return $this->borderColor;
    }

    public function setBorderColor(?string $borderColor): static
    {
        $this->borderColor = $borderColor;

        return $this;
    }
}
