<?php

namespace App\Entity;

use App\Repository\FileSystemNodeTagMappingRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FileSystemNodeTagMappingRepository::class)]
class FileSystemNodeTagMapping
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'fileSystemNodeTagMappings')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?FileSystemNodeTag $fileSystemNodeTag = null;

    #[ORM\ManyToOne(inversedBy: 'fileSystemNodeTagMappings')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?FilesystemNode $fileSystemNode = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFileSystemNodeTag(): ?FileSystemNodeTag
    {
        return $this->fileSystemNodeTag;
    }

    public function setFileSystemNodeTag(?FileSystemNodeTag $fileSystemNodeTag): static
    {
        $this->fileSystemNodeTag = $fileSystemNodeTag;

        return $this;
    }

    public function getFileSystemNode(): ?FilesystemNode
    {
        return $this->fileSystemNode;
    }

    public function setFileSystemNode(?FilesystemNode $fileSystemNode): static
    {
        $this->fileSystemNode = $fileSystemNode;

        return $this;
    }
}
