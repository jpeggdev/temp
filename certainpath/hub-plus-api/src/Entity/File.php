<?php

namespace App\Entity;

use App\Repository\FileRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FileRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ORM\Index(name: 'idx_file_md5_hash', columns: ['md5_hash'])]
class File extends FilesystemNode
{
    #[ORM\Column(length: 255)]
    private ?string $originalFilename = null;

    #[ORM\Column(length: 255)]
    private ?string $bucketName = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $objectKey = null;

    #[ORM\Column(length: 255)]
    private ?string $contentType = null;

    #[ORM\OneToOne(mappedBy: 'file', cascade: ['persist', 'remove'])]
    private ?FileTmp $fileTmp = null;

    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    private ?string $mimeType = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $url = null;

    #[ORM\Column(type: 'string', length: 32, nullable: true)]
    private ?string $md5Hash = null;

    public function __construct()
    {
        parent::__construct();
    }

    public function getOriginalFilename(): ?string
    {
        return $this->originalFilename;
    }

    public function setOriginalFilename(string $originalFilename): static
    {
        $this->originalFilename = $originalFilename;

        return $this;
    }

    public function getBucketName(): ?string
    {
        return $this->bucketName;
    }

    public function setBucketName(string $bucketName): static
    {
        $this->bucketName = $bucketName;

        return $this;
    }

    public function getObjectKey(): ?string
    {
        return $this->objectKey;
    }

    public function setObjectKey(string $objectKey): static
    {
        $this->objectKey = $objectKey;

        return $this;
    }

    public function getContentType(): ?string
    {
        return $this->contentType;
    }

    public function setContentType(string $contentType): static
    {
        $this->contentType = $contentType;

        return $this;
    }

    public function getFileTmp(): ?FileTmp
    {
        return $this->fileTmp;
    }

    public function setFileTmp(FileTmp $fileTmp): static
    {
        // set the owning side of the relation if necessary
        if ($fileTmp->getFile() !== $this) {
            $fileTmp->setFile($this);
        }

        $this->fileTmp = $fileTmp;

        return $this;
    }

    public function getMimeType(): ?string
    {
        return $this->mimeType;
    }

    public function setMimeType(?string $mimeType): static
    {
        $this->mimeType = $mimeType;

        return $this;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(?string $url): static
    {
        $this->url = $url;

        return $this;
    }

    public function getMd5Hash(): ?string
    {
        return $this->md5Hash;
    }

    public function setMd5Hash(?string $md5Hash): static
    {
        $this->md5Hash = $md5Hash;

        return $this;
    }
}
