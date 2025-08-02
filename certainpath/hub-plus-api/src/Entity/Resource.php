<?php

namespace App\Entity;

use App\Doctrine\Types\TsVectorType;
use App\Entity\Trait\TimestampableDateTimeTZTrait;
use App\Entity\Trait\UuidTrait;
use App\Repository\ResourceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ResourceRepository::class)]
#[ORM\UniqueConstraint(fields: ['uuid'])]
#[ORM\UniqueConstraint(fields: ['slug'])]
#[ORM\HasLifecycleCallbacks]
class Resource
{
    use TimestampableDateTimeTZTrait;
    use UuidTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * @var Collection<int, ResourceContentBlock>
     */
    #[ORM\OneToMany(targetEntity: ResourceContentBlock::class, mappedBy: 'resource')]
    private Collection $resourceContentBlocks;

    /**
     * @var Collection<int, ResourceTagMapping>
     */
    #[ORM\OneToMany(targetEntity: ResourceTagMapping::class, mappedBy: 'resource')]
    private Collection $resourceTagMappings;

    /**
     * @var Collection<int, ResourceCategoryMapping>
     */
    #[ORM\OneToMany(targetEntity: ResourceCategoryMapping::class, mappedBy: 'resource')]
    private Collection $resourceCategoryMappings;

    /**
     * @var Collection<int, ResourceTradeMapping>
     */
    #[ORM\OneToMany(targetEntity: ResourceTradeMapping::class, mappedBy: 'resource')]
    private Collection $resourceTradeMappings;

    /**
     * @var Collection<int, ResourceEmployeeRoleMapping>
     */
    #[ORM\OneToMany(targetEntity: ResourceEmployeeRoleMapping::class, mappedBy: 'resource')]
    private Collection $resourceEmployeeRoleMappings;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $description = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $tagline = null;

    #[ORM\Column(type: Types::DATETIMETZ_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $publishStartDate = null;

    #[ORM\Column(type: Types::DATETIMETZ_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $publishEndDate = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $contentUrl = null;

    private ?string $contentFilename = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $thumbnailUrl = null;

    #[ORM\ManyToOne(inversedBy: 'resources')]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?ResourceType $type = null;

    #[ORM\Column(nullable: false, options: ['default' => 'false'])]
    private ?bool $isPublished = false;

    #[ORM\ManyToOne]
    private ?File $thumbnail = null;

    #[ORM\Column(nullable: false, options: ['default' => 'false'])]
    private ?bool $isFeatured = false;

    #[ORM\Column(type: TsVectorType::NAME, nullable: true)]
    private ?string $searchVector = null;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private int $viewCount = 0;

    /**
     * @var Collection<int, ResourceFavorite>
     */
    #[ORM\OneToMany(targetEntity: ResourceFavorite::class, mappedBy: 'resource')]
    private Collection $resourceFavorites;

    /**
     * @var Collection<int, ResourceRelation>
     */
    #[ORM\OneToMany(targetEntity: ResourceRelation::class, mappedBy: 'resource')]
    private Collection $resourceRelations;

    #[ORM\Column(length: 255)]
    private ?string $slug = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $legacyUrl;

    public function __construct()
    {
        $this->resourceContentBlocks = new ArrayCollection();
        $this->resourceTagMappings = new ArrayCollection();
        $this->resourceCategoryMappings = new ArrayCollection();
        $this->resourceTradeMappings = new ArrayCollection();
        $this->resourceEmployeeRoleMappings = new ArrayCollection();
        $this->resourceFavorites = new ArrayCollection();
        $this->resourceRelations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Collection<int, ResourceContentBlock>
     */
    public function getResourceContentBlocks(): Collection
    {
        return $this->resourceContentBlocks;
    }

    public function addResourceContentBlock(ResourceContentBlock $resourceContentBlock): static
    {
        if (!$this->resourceContentBlocks->contains($resourceContentBlock)) {
            $this->resourceContentBlocks->add($resourceContentBlock);
            $resourceContentBlock->setResource($this);
        }

        return $this;
    }

    public function removeResourceContentBlock(ResourceContentBlock $resourceContentBlock): static
    {
        if ($this->resourceContentBlocks->removeElement($resourceContentBlock)) {
            // set the owning side to null (unless already changed)
            if ($resourceContentBlock->getResource() === $this) {
                $resourceContentBlock->setResource(null);
            }
        }

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
            $resourceTagMapping->setResource($this);
        }

        return $this;
    }

    public function removeResourceTagMapping(ResourceTagMapping $resourceTagMapping): static
    {
        if ($this->resourceTagMappings->removeElement($resourceTagMapping)) {
            // set the owning side to null (unless already changed)
            if ($resourceTagMapping->getResource() === $this) {
                $resourceTagMapping->setResource(null);
            }
        }

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
            $resourceCategoryMapping->setResource($this);
        }

        return $this;
    }

    public function removeResourceCategoryMapping(ResourceCategoryMapping $resourceCategoryMapping): static
    {
        if ($this->resourceCategoryMappings->removeElement($resourceCategoryMapping)) {
            // set the owning side to null (unless already changed)
            if ($resourceCategoryMapping->getResource() === $this) {
                $resourceCategoryMapping->setResource(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ResourceTradeMapping>
     */
    public function getResourceTradeMappings(): Collection
    {
        return $this->resourceTradeMappings;
    }

    public function addResourceTradeMapping(ResourceTradeMapping $resourceTradeMapping): static
    {
        if (!$this->resourceTradeMappings->contains($resourceTradeMapping)) {
            $this->resourceTradeMappings->add($resourceTradeMapping);
            $resourceTradeMapping->setResource($this);
        }

        return $this;
    }

    public function removeResourceTradeMapping(ResourceTradeMapping $resourceTradeMapping): static
    {
        if ($this->resourceTradeMappings->removeElement($resourceTradeMapping)) {
            // set the owning side to null (unless already changed)
            if ($resourceTradeMapping->getResource() === $this) {
                $resourceTradeMapping->setResource(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ResourceEmployeeRoleMapping>
     */
    public function getResourceEmployeeRoleMappings(): Collection
    {
        return $this->resourceEmployeeRoleMappings;
    }

    public function addResourceEmployeeRoleMapping(ResourceEmployeeRoleMapping $resourceEmployeeRoleMapping): static
    {
        if (!$this->resourceEmployeeRoleMappings->contains($resourceEmployeeRoleMapping)) {
            $this->resourceEmployeeRoleMappings->add($resourceEmployeeRoleMapping);
            $resourceEmployeeRoleMapping->setResource($this);
        }

        return $this;
    }

    public function removeResourceEmployeeRoleMapping(ResourceEmployeeRoleMapping $resourceEmployeeRoleMapping): static
    {
        if ($this->resourceEmployeeRoleMappings->removeElement($resourceEmployeeRoleMapping)) {
            // set the owning side to null (unless already changed)
            if ($resourceEmployeeRoleMapping->getResource() === $this) {
                $resourceEmployeeRoleMapping->setResource(null);
            }
        }

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getTagline(): ?string
    {
        return $this->tagline;
    }

    public function setTagline(?string $tagline): static
    {
        $this->tagline = $tagline;

        return $this;
    }

    public function getPublishStartDate(): ?\DateTimeInterface
    {
        return $this->publishStartDate;
    }

    public function setPublishStartDate(?\DateTimeInterface $publishStartDate): static
    {
        $this->publishStartDate = $publishStartDate;

        return $this;
    }

    public function getPublishEndDate(): ?\DateTimeInterface
    {
        return $this->publishEndDate;
    }

    public function setPublishEndDate(?\DateTimeInterface $publishEndDate): static
    {
        $this->publishEndDate = $publishEndDate;

        return $this;
    }

    public function getContentUrl(): ?string
    {
        return $this->contentUrl;
    }

    public function setContentUrl(?string $contentUrl): static
    {
        $this->contentUrl = $contentUrl;

        return $this;
    }

    public function getThumbnailUrl(): ?string
    {
        return $this->thumbnailUrl;
    }

    public function setThumbnailUrl(?string $thumbnailUrl): static
    {
        $this->thumbnailUrl = $thumbnailUrl;

        return $this;
    }

    public function getType(): ?ResourceType
    {
        return $this->type;
    }

    public function setType(?ResourceType $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function isPublished(): ?bool
    {
        return $this->isPublished;
    }

    public function setIsPublished(bool $isPublished): static
    {
        $this->isPublished = $isPublished;

        return $this;
    }

    public function getThumbnail(): ?File
    {
        return $this->thumbnail;
    }

    public function setThumbnail(?File $thumbnail): static
    {
        $this->thumbnail = $thumbnail;

        return $this;
    }

    public function isFeatured(): ?bool
    {
        return $this->isFeatured;
    }

    public function setIsFeatured(bool $isFeatured): static
    {
        $this->isFeatured = $isFeatured;

        return $this;
    }

    public function getSearchVector(): ?string
    {
        return $this->searchVector;
    }

    public function setSearchVector(?string $searchVector): void
    {
        $this->searchVector = $searchVector;
    }

    public function getViewCount(): ?int
    {
        return $this->viewCount;
    }

    public function setViewCount(?int $viewCount): void
    {
        $this->viewCount = $viewCount;
    }

    public function incrementViewCount(): static
    {
        ++$this->viewCount;

        return $this;
    }

    /**
     * @return Collection<int, ResourceFavorite>
     */
    public function getResourceFavorites(): Collection
    {
        return $this->resourceFavorites;
    }

    public function addResourceFavorite(ResourceFavorite $resourceFavorite): static
    {
        if (!$this->resourceFavorites->contains($resourceFavorite)) {
            $this->resourceFavorites->add($resourceFavorite);
            $resourceFavorite->setResource($this);
        }

        return $this;
    }

    public function removeResourceFavorite(ResourceFavorite $resourceFavorite): static
    {
        if ($this->resourceFavorites->removeElement($resourceFavorite)) {
            // set the owning side to null (unless already changed)
            if ($resourceFavorite->getResource() === $this) {
                $resourceFavorite->setResource(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ResourceRelation>
     */
    public function getResourceRelations(): Collection
    {
        return $this->resourceRelations;
    }

    public function addResourceRelation(ResourceRelation $resourceRelation): static
    {
        if (!$this->resourceRelations->contains($resourceRelation)) {
            $this->resourceRelations->add($resourceRelation);
            $resourceRelation->setResource($this);
        }

        return $this;
    }

    public function removeResourceRelation(ResourceRelation $resourceRelation): static
    {
        if ($this->resourceRelations->removeElement($resourceRelation)) {
            // set the owning side to null (unless already changed)
            if ($resourceRelation->getResource() === $this) {
                $resourceRelation->setResource(null);
            }
        }

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): static
    {
        $this->slug = $slug;

        return $this;
    }

    public function getLegacyUrl(): ?string
    {
        return $this->legacyUrl;
    }

    public function setLegacyUrl(?string $legacyUrl): static
    {
        $this->legacyUrl = $legacyUrl;

        return $this;
    }

    public function getContentFilename(): ?string
    {
        return $this->contentFilename;
    }

    public function setContentFilename(?string $contentFilename): static
    {
        $this->contentFilename = $contentFilename;

        return $this;
    }
}
