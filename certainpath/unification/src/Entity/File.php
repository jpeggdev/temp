<?php

namespace App\Entity;

use App\Repository\FileRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FileRepository::class)]
#[ORM\HasLifecycleCallbacks]
class File
{
    use Traits\TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $originalFilename = null;

    #[ORM\Column(length: 255)]
    private ?string $bucketName = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $objectKey = null;

    #[ORM\Column(length: 255)]
    private ?string $contentType = null;

    /**
     * @var Collection<int, CampaignFile>
     */
    #[ORM\OneToMany(targetEntity: CampaignFile::class, mappedBy: 'file')]
    private Collection $campaignFiles;

    public function __construct()
    {
        $this->campaignFiles = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    /**
     * @return Collection<int, CampaignFile>
     */
    public function getCampaignFiles(): Collection
    {
        return $this->campaignFiles;
    }

    public function addCampaignFile(CampaignFile $campaignFile): static
    {
        if (!$this->campaignFiles->contains($campaignFile)) {
            $this->campaignFiles->add($campaignFile);
            $campaignFile->setFile($this);
        }

        return $this;
    }

    public function removeCampaignFile(CampaignFile $campaignFile): static
    {
        if ($this->campaignFiles->removeElement($campaignFile)) {
            // set the owning side to null (unless already changed)
            if ($campaignFile->getFile() === $this) {
                $campaignFile->setFile(null);
            }
        }

        return $this;
    }
}
