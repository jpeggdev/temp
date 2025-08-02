<?php

namespace App\Entity;

use App\Repository\CampaignIterationRepository;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CampaignIterationRepository::class)]
#[ORM\HasLifecycleCallbacks]
class CampaignIteration
{
    use Traits\TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'campaignIterations')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Campaign $campaign = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?CampaignIterationStatus $campaignIterationStatus = null;

    #[ORM\Column]
    private ?int $iterationNumber = null;

    /**
     * @var Collection<int, CampaignIterationWeek>
     */
    #[ORM\OneToMany(targetEntity: CampaignIterationWeek::class, mappedBy: 'campaignIteration', orphanRemoval: true)]
    private Collection $campaignIterationWeeks;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?DateTimeInterface $startDate = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?DateTimeInterface $endDate = null;

    /**
     * @var Collection<int, Batch>
     */
    #[ORM\OneToMany(targetEntity: Batch::class, mappedBy: 'campaignIteration', orphanRemoval: true)]
    private Collection $batches;

    public function __construct()
    {
        $this->campaignIterationWeeks = new ArrayCollection();
        $this->batches = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getCampaignIterationStatus(): ?CampaignIterationStatus
    {
        return $this->campaignIterationStatus;
    }

    public function setCampaignIterationStatus(?CampaignIterationStatus $campaignIterationStatus): static
    {
        $this->campaignIterationStatus = $campaignIterationStatus;

        return $this;
    }

    public function getIterationNumber(): ?int
    {
        return $this->iterationNumber;
    }

    public function setIterationNumber(int $iterationNumber): static
    {
        $this->iterationNumber = $iterationNumber;

        return $this;
    }

    public function getStartDate(): ?DateTimeInterface
    {
        return $this->startDate;
    }

    public function setStartDate(DateTimeInterface $startDate): static
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getEndDate(): ?DateTimeInterface
    {
        return $this->endDate;
    }

    public function setEndDate(DateTimeInterface $endDate): static
    {
        $this->endDate = $endDate;

        return $this;
    }

    /**
     * @return Collection<int, CampaignIterationWeek>
     */
    public function getCampaignIterationWeeks(): Collection
    {
        return $this->campaignIterationWeeks;
    }

    public function addCampaignIterationWeek(CampaignIterationWeek $campaignIterationWeek): static
    {
        if (!$this->campaignIterationWeeks->contains($campaignIterationWeek)) {
            $this->campaignIterationWeeks->add($campaignIterationWeek);
            $campaignIterationWeek->setCampaignIteration($this);
        }

        return $this;
    }

    public function removeCampaignIterationWeek(CampaignIterationWeek $campaignIterationWeek): static
    {
        if ($this->campaignIterationWeeks->removeElement($campaignIterationWeek)) {
            // set the owning side to null (unless already changed)
            if ($campaignIterationWeek->getCampaignIteration() === $this) {
                $campaignIterationWeek->setCampaignIteration(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Batch>
     */
    public function getBatches(): Collection
    {
        return $this->batches;
    }

    public function addBatch(Batch $batch): static
    {
        if (!$this->batches->contains($batch)) {
            $this->batches->add($batch);
            $batch->setCampaignIteration($this);
        }

        return $this;
    }

    public function removeBatch(Batch $batch): static
    {
        if ($this->batches->removeElement($batch)) {
            // set the owning side to null (unless already changed)
            if ($batch->getCampaignIteration() === $this) {
                $batch->setCampaignIteration(null);
            }
        }

        return $this;
    }

    public function isCompleted(): bool
    {
        return $this->getCampaignIterationStatus()?->getName() === CampaignIterationStatus::STATUS_COMPLETED;
    }

    public function isPaused(): bool
    {
        return $this->getCampaignIterationStatus()?->getName() === CampaignIterationStatus::STATUS_PAUSED;
    }
}
