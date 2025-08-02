<?php

namespace App\Entity;

use App\Doctrine\Types\TsVectorType;
use App\Entity\Trait\UuidTrait;
use App\Repository\FilesystemNodeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

#[ORM\Entity(repositoryClass: FilesystemNodeRepository::class)]
#[ORM\InheritanceType('SINGLE_TABLE')]
#[ORM\DiscriminatorColumn(name: 'type', type: 'string')]
#[ORM\DiscriminatorMap(['file' => 'File', 'folder' => 'Folder'])]
#[ORM\UniqueConstraint(fields: ['uuid'])]
#[ORM\UniqueConstraint(fields: ['parent', 'name'])]
#[ORM\Index(name: 'idx_filesystem_node_search_vector', columns: ['search_vector'])]
#[ORM\HasLifecycleCallbacks]
abstract class FilesystemNode
{
    use TimestampableEntity;
    use UuidTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column]
    protected ?int $id = null;

    #[ORM\Column(length: 255)]
    protected ?string $name = null;

    #[ORM\ManyToOne(targetEntity: Folder::class, inversedBy: 'children')]
    #[ORM\JoinColumn(nullable: true)]
    protected ?Folder $parent = null;

    #[ORM\ManyToOne(inversedBy: 'filesystemNodes')]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?Employee $createdBy = null;

    #[ORM\Column(type: TsVectorType::NAME, nullable: true)]
    private ?string $searchVector = null;

    /**
     * @var Collection<int, FileSystemNodeTagMapping>
     */
    #[ORM\OneToMany(targetEntity: FileSystemNodeTagMapping::class, mappedBy: 'fileSystemNode')]
    private Collection $fileSystemNodeTagMappings;

    #[ORM\Column(type: 'bigint', nullable: true)]
    protected ?int $fileSize = null;

    #[ORM\Column(length: 255)]
    private ?string $fileType = null;

    #[ORM\ManyToOne(inversedBy: 'updatedFilesystemNodes')]
    private ?Employee $updatedBy = null;

    public function __construct()
    {
        $this->fileSystemNodeTagMappings = new ArrayCollection();
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

    public function getParent(): ?Folder
    {
        return $this->parent;
    }

    public function setParent(?Folder $parent): static
    {
        $this->parent = $parent;

        return $this;
    }

    public function getCreatedBy(): ?Employee
    {
        return $this->createdBy;
    }

    public function setCreatedBy(?Employee $createdBy): static
    {
        $this->createdBy = $createdBy;

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

    /**
     * @return Collection<int, FileSystemNodeTagMapping>
     */
    public function getFileSystemNodeTagMappings(): Collection
    {
        return $this->fileSystemNodeTagMappings;
    }

    public function addFileSystemNodeTagMapping(FileSystemNodeTagMapping $fileSystemNodeTagMapping): static
    {
        if (!$this->fileSystemNodeTagMappings->contains($fileSystemNodeTagMapping)) {
            $this->fileSystemNodeTagMappings->add($fileSystemNodeTagMapping);
            $fileSystemNodeTagMapping->setFileSystemNode($this);
        }

        return $this;
    }

    public function removeFileSystemNodeTagMapping(FileSystemNodeTagMapping $fileSystemNodeTagMapping): static
    {
        if ($this->fileSystemNodeTagMappings->removeElement($fileSystemNodeTagMapping)) {
            // set the owning side to null (unless already changed)
            if ($fileSystemNodeTagMapping->getFileSystemNode() === $this) {
                $fileSystemNodeTagMapping->setFileSystemNode(null);
            }
        }

        return $this;
    }

    public function getFileSize(): ?int
    {
        return $this->fileSize;
    }

    public function setFileSize(?int $fileSize): static
    {
        $this->fileSize = $fileSize;

        return $this;
    }

    public function getFileType(): ?string
    {
        return $this->fileType;
    }

    public function setFileType(string $fileType): static
    {
        $this->fileType = $fileType;

        return $this;
    }

    public function getUpdatedBy(): ?Employee
    {
        return $this->updatedBy;
    }

    public function setUpdatedBy(?Employee $updatedBy): static
    {
        $this->updatedBy = $updatedBy;

        return $this;
    }
}
