<?php

namespace App\Entity;

use App\Repository\FileSystemNodeTagRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

#[ORM\Entity(repositoryClass: FileSystemNodeTagRepository::class)]
#[ORM\HasLifecycleCallbacks]
class FileSystemNodeTag
{
    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    /**
     * @var Collection<int, FileSystemNodeTagMapping>
     */
    #[ORM\OneToMany(targetEntity: FileSystemNodeTagMapping::class, mappedBy: 'fileSystemNodeTag')]
    private Collection $fileSystemNodeTagMappings;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $color = null;

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
            $fileSystemNodeTagMapping->setFileSystemNodeTag($this);
        }

        return $this;
    }

    public function removeFileSystemNodeTagMapping(FileSystemNodeTagMapping $fileSystemNodeTagMapping): static
    {
        if ($this->fileSystemNodeTagMappings->removeElement($fileSystemNodeTagMapping)) {
            // set the owning side to null (unless already changed)
            if ($fileSystemNodeTagMapping->getFileSystemNodeTag() === $this) {
                $fileSystemNodeTagMapping->setFileSystemNodeTag(null);
            }
        }

        return $this;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(?string $color): static
    {
        $this->color = $color;

        return $this;
    }
}
