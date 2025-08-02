<?php

namespace App\Entity;

use App\Entity\Traits\TimestampableEntity;
use App\Repository\BatchRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BatchRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Batch
{
    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?BatchStatus $batchStatus = null;

    #[ORM\ManyToOne(inversedBy: 'batches')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Campaign $campaign = null;

    #[ORM\ManyToOne(inversedBy: 'batches')]
    #[ORM\JoinColumn(nullable: false)]
    private ?CampaignIteration $campaignIteration = null;

    #[ORM\OneToOne(inversedBy: 'batch')]
    #[ORM\JoinColumn(nullable: false)]
    private ?CampaignIterationWeek $campaignIterationWeek = null;

    /**
     * @var Collection<int, Prospect>
     */
    #[ORM\ManyToMany(targetEntity: Prospect::class, mappedBy: 'batches')]
    private Collection $prospects;

    public function __construct()
    {
        $this->prospects = new ArrayCollection();
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getBatchStatus(): ?BatchStatus
    {
        return $this->batchStatus;
    }

    public function setBatchStatus(?BatchStatus $batchStatus): static
    {
        $this->batchStatus = $batchStatus;

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

    public function getCampaignIteration(): ?CampaignIteration
    {
        return $this->campaignIteration;
    }

    public function setCampaignIteration(?CampaignIteration $campaignIteration): static
    {
        $this->campaignIteration = $campaignIteration;

        return $this;
    }

    public function getCampaignIterationWeek(): ?CampaignIterationWeek
    {
        return $this->campaignIterationWeek;
    }

    public function setCampaignIterationWeek(?CampaignIterationWeek $campaignIterationWeek): static
    {
        $this->campaignIterationWeek = $campaignIterationWeek;

        return $this;
    }

    /**
     * @return Collection<int, Prospect>
     */
    public function getProspects(): Collection
    {
        return $this->prospects;
    }

    public function addProspect(Prospect $prospect): static
    {
        if (!$this->prospects->contains($prospect)) {
            $this->prospects->add($prospect);
            $prospect->addBatch($this);
        }

        return $this;
    }

    public function removeProspect(Prospect $prospect): static
    {
        $this->prospects->removeElement($prospect);

        return $this;
    }

    public function isNew(): bool
    {
        return $this->batchStatus->getName() === BatchStatus::STATUS_NEW;
    }
}
