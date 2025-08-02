<?php

namespace App\Entity;

use App\Repository\FolderRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FolderRepository::class)]
#[ORM\UniqueConstraint(fields: ['path'])]
#[ORM\Index(fields: ['path'])]
class Folder extends FilesystemNode
{
    #[ORM\Column(length: 1024)]
    private ?string $path = null;

    /**
     * @var Collection<int, FilesystemNode>
     */
    #[ORM\OneToMany(targetEntity: FilesystemNode::class, mappedBy: 'parent')]
    private Collection $children;

    public function __construct()
    {
        $this->children = new ArrayCollection();

        parent::__construct();
    }

    public function getPath(): ?string
    {
        return $this->path;
    }

    public function setPath(string $path): static
    {
        $this->path = $path;

        return $this;
    }

    /**
     * @return Collection<int, FilesystemNode>
     */
    public function getChildren(): Collection
    {
        return $this->children;
    }

    /**
     * Get only folder children.
     *
     * @return Collection<int, Folder>
     */
    public function getFolders(): Collection
    {
        /** @var Collection<int, Folder> $folders */
        $folders = $this->children->filter(fn ($node) => $node instanceof Folder);

        return $folders;
    }

    /**
     * Get only file children.
     *
     * @return Collection<int, File>
     */
    public function getFiles(): Collection
    {
        /** @var Collection<int, File> $files */
        $files = $this->children->filter(fn ($node) => $node instanceof File);

        return $files;
    }

    public function addChild(FilesystemNode $child): static
    {
        if (!$this->children->contains($child)) {
            $this->children->add($child);
            $child->setParent($this);
        }

        return $this;
    }

    public function removeChild(FilesystemNode $child): static
    {
        if ($this->children->removeElement($child)) {
            // set the owning side to null (unless already changed)
            if ($child->getParent() === $this) {
                $child->setParent(null);
            }
        }

        return $this;
    }
}
