<?php

namespace App\Entity;

use App\Repository\CampaignRepository;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CampaignRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Campaign
{
    use Traits\StatusEntity;
    use Traits\TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\ManyToOne(inversedBy: 'campaigns')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Company $company = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?CampaignStatus $campaignStatus = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?DateTimeInterface $startDate = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?DateTimeInterface $endDate = null;

    #[ORM\Column(type: Types::INTEGER)]
    private ?int $mailingFrequencyWeeks = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $phoneNumber = null;

    /**
     * @var Collection<int, CampaignIteration>
     */
    #[ORM\OneToMany(targetEntity: CampaignIteration::class, mappedBy: 'campaign', orphanRemoval: true)]
    private Collection $campaignIterations;

    #[ORM\OneToOne(targetEntity: MailPackage::class, inversedBy: 'campaign')]
    #[ORM\JoinColumn(nullable: false)]
    private ?MailPackage $mailPackage = null;

    /**
     * @var Collection<int, Batch>
     */
    #[ORM\OneToMany(targetEntity: Batch::class, mappedBy: 'campaign')]
    private Collection $batches;

    /**
     * @var Collection<int, CampaignFile>
     */
    #[ORM\OneToMany(targetEntity: CampaignFile::class, mappedBy: 'campaign')]
    private Collection $campaignFiles;

    /**
     * @var Collection<int, ProspectFilterRule>
     */
    #[ORM\ManyToMany(targetEntity: ProspectFilterRule::class)]
    private Collection $prospectFilterRules;

    #[ORM\Column(type: Types::JSON, nullable: false, options: ['default' => '[]'])]
    private array $mailingDropWeeks;

    /**
     * @var Collection<int, CampaignEvent>
     */
    #[ORM\OneToMany(targetEntity: CampaignEvent::class, mappedBy: 'campaign')]
    private Collection $campaignEvents;

    #[ORM\Column(nullable: true)]
    private ?int $hubPlusProductId = null;

    /**
     * @var Collection<int, Location>
     */
    #[ORM\ManyToMany(targetEntity: Location::class, inversedBy: 'campaigns')]
    private Collection $locations;

    public function __construct()
    {
        $this->campaignIterations = new ArrayCollection();
        $this->batches = new ArrayCollection();
        $this->campaignFiles = new ArrayCollection();
        $this->prospectFilterRules = new ArrayCollection();
        $this->mailingDropWeeks = [];
        $this->campaignEvents = new ArrayCollection();
        $this->locations = new ArrayCollection();
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

    public function getCompany(): ?Company
    {
        return $this->company;
    }

    public function setCompany(?Company $company): static
    {
        $this->company = $company;

        return $this;
    }

    public function getCampaignStatus(): ?CampaignStatus
    {
        return $this->campaignStatus;
    }

    public function setCampaignStatus(?CampaignStatus $campaignStatus): static
    {
        $this->campaignStatus = $campaignStatus;

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

    public function getMailingFrequencyWeeks(): ?int
    {
        return $this->mailingFrequencyWeeks;
    }

    public function setMailingFrequencyWeeks(int $mailingFrequencyWeeks): static
    {
        $this->mailingFrequencyWeeks = $mailingFrequencyWeeks;

        return $this;
    }

    public function getPhoneNumber(): ?string
    {
        return $this->phoneNumber;
    }

    public function setPhoneNumber(?string $phoneNumber): static
    {
        $this->phoneNumber = $phoneNumber;

        return $this;
    }

    /**
     * @return Collection<int, CampaignIteration>
     */
    public function getCampaignIterations(): Collection
    {
        return $this->campaignIterations;
    }

    public function addCampaignIteration(CampaignIteration $campaignIteration): static
    {
        if (!$this->campaignIterations->contains($campaignIteration)) {
            $this->campaignIterations->add($campaignIteration);
            $campaignIteration->setCampaign($this);
        }

        return $this;
    }

    public function removeCampaignIteration(CampaignIteration $campaignIteration): static
    {
        if ($this->campaignIterations->removeElement($campaignIteration)) {
            // set the owning side to null (unless already changed)
            if ($campaignIteration->getCampaign() === $this) {
                $campaignIteration->setCampaign(null);
            }
        }

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
            $batch->setCampaign($this);
        }

        return $this;
    }

    public function removeBatch(Batch $batch): static
    {
        if ($this->batches->removeElement($batch)) {
            // set the owning side to null (unless already changed)
            if ($batch->getCampaign() === $this) {
                $batch->setCampaign(null);
            }
        }

        return $this;
    }

    public function isActive(): bool
    {
        return $this->getCampaignStatus()?->getName() === CampaignStatus::STATUS_ACTIVE;
    }

    public function isCompleted(): bool
    {
        return $this->getCampaignStatus()?->getName() === CampaignStatus::STATUS_COMPLETED;
    }

    public function isPaused(): bool
    {
        return $this->getCampaignStatus()?->getName() === CampaignStatus::STATUS_PAUSED;
    }

    public function canBePaused(): bool
    {
        return $this->isActive();
    }

    public function canBeResumed(): bool
    {
        return $this->isPaused() && !$this->isCompleted();
    }

    public function canBeStopped(): bool
    {
        return $this->isActive() && !$this->isCompleted();
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
            $campaignFile->setCampaign($this);
        }

        return $this;
    }

    public function removeCampaignFile(CampaignFile $campaignFile): static
    {
        if ($this->campaignFiles->removeElement($campaignFile)) {
            // set the owning side to null (unless already changed)
            if ($campaignFile->getCampaign() === $this) {
                $campaignFile->setCampaign(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ProspectFilterRule>
     */
    public function getProspectFilterRules(): Collection
    {
        return $this->prospectFilterRules;
    }

    public function addProspectFilterRule(ProspectFilterRule $prospectFilterRule): static
    {
        if (!$this->prospectFilterRules->contains($prospectFilterRule)) {
            $this->prospectFilterRules->add($prospectFilterRule);
        }

        return $this;
    }

    /**
     * @param ArrayCollection<int, ProspectFilterRule> $prospectFilterRules
     */
    public function addProspectFilterRules(ArrayCollection $prospectFilterRules): static
    {
        foreach ($prospectFilterRules as $prospectFilterRule) {
            $this->addProspectFilterRule($prospectFilterRule);
        }

        return $this;
    }

    public function removeProspectFilterRule(ProspectFilterRule $prospectFilterRule): static
    {
        $this->prospectFilterRules->removeElement($prospectFilterRule);

        return $this;
    }

    public function getMailingDropWeeks(): array
    {
        return $this->mailingDropWeeks;
    }

    public function setMailingDropWeeks(array $mailingDropWeeks): static
    {
        $this->mailingDropWeeks = $mailingDropWeeks;

        return $this;
    }

    /**
     * @return Collection<int, CampaignEvent>
     */
    public function getCampaignEvents(): Collection
    {
        return $this->campaignEvents;
    }

    public function addCampaignEvent(CampaignEvent $campaignEvent): static
    {
        if (!$this->campaignEvents->contains($campaignEvent)) {
            $this->campaignEvents->add($campaignEvent);
            $campaignEvent->setCampaign($this);
        }

        return $this;
    }

    public function removeCampaignEvent(CampaignEvent $campaignEvent): static
    {
        if ($this->campaignEvents->removeElement($campaignEvent)) {
            // set the owning side to null (unless already changed)
            if ($campaignEvent->getCampaign() === $this) {
                $campaignEvent->setCampaign(null);
            }
        }

        return $this;
    }

    public function getHubPlusProductId(): ?int
    {
        return $this->hubPlusProductId;
    }

    public function setHubPlusProductId(?int $hubPlusProductId): static
    {
        $this->hubPlusProductId = $hubPlusProductId;

        return $this;
    }

    /**
     * @return Collection<int, Location>
     */
    public function getLocations(): Collection
    {
        return $this->locations;
    }

    public function addLocation(Location $location): static
    {
        if (!$this->locations->contains($location)) {
            $this->locations->add($location);
        }

        return $this;
    }

    public function removeLocation(Location $location): static
    {
        $this->locations->removeElement($location);

        return $this;
    }
}
