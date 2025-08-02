<?php

namespace App\Entity;

use App\Repository\CampaignFileRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CampaignFileRepository::class)]
#[ORM\HasLifecycleCallbacks]
class CampaignFile
{
    use Traits\TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'campaignFiles')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?File $file = null;

    #[ORM\ManyToOne(inversedBy: 'campaignFiles')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Campaign $campaign = null;

    #[ORM\ManyToOne(inversedBy: 'campaignFiles')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?MailPackage $mailPackage = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFile(): ?File
    {
        return $this->file;
    }

    public function setFile(?File $file): static
    {
        $this->file = $file;

        return $this;
    }

    public function getCampaign(): ?Campaign
    {
        return $this->campaign;
    }

    public function setCampaign(?Campaign $campaign): static
    {
        $this->campaign = $campaign;

        return $this;
    }

    public function getMailPackage(): ?MailPackage
    {
        return $this->mailPackage;
    }

    public function setMailPackage(?MailPackage $mailPackage): static
    {
        $this->mailPackage = $mailPackage;

        return $this;
    }
}
