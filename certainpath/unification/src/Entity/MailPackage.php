<?php

namespace App\Entity;

use App\Repository\MailPackageRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MailPackageRepository::class)]
#[ORM\Index(name: 'mail_package_name_idx', columns: ['name'])]
#[ORM\HasLifecycleCallbacks]
#[ORM\Index(name: 'mail_package_external_id_idx', columns: ['external_id'])]
class MailPackage
{
    use Traits\ExternalIdEntity;
    use Traits\StatusEntity;
    use Traits\TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $series = null;

    /**
     * @var Collection<int, CampaignFile>
     */
    #[ORM\OneToMany(targetEntity: CampaignFile::class, mappedBy: 'mailPackage')]
    private Collection $campaignFiles;

    #[ORM\OneToOne(targetEntity: Campaign::class, mappedBy: 'mailPackage')]
    private ?Campaign $campaign = null;

    public function __construct()
    {
        $this->campaignFiles = new ArrayCollection();
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

    public function getSeries(): ?string
    {
        return $this->series;
    }

    public function setSeries(string $series): static
    {
        $this->series = $series;

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
            $campaignFile->setMailPackage($this);
        }

        return $this;
    }

    public function removeCampaignFile(CampaignFile $campaignFile): static
    {
        if ($this->campaignFiles->removeElement($campaignFile)) {
            // set the owning side to null (unless already changed)
            if ($campaignFile->getMailPackage() === $this) {
                $campaignFile->setMailPackage(null);
            }
        }

        return $this;
    }

    public function getCampaign(): ?Campaign
    {
        return $this->campaign;
    }

    public function setCampaign(Campaign $campaign): static
    {
        // set the owning side of the relation if necessary
        if ($campaign->getMailPackage() !== $this) {
            $campaign->setMailPackage($this);
        }

        $this->campaign = $campaign;

        return $this;
    }
}
