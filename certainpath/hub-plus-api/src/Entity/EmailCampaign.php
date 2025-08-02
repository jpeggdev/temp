<?php

namespace App\Entity;

use App\Entity\Trait\TimestampableDateTimeTZTrait;
use App\Repository\EmailCampaignRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\HasLifecycleCallbacks]
#[ORM\Entity(repositoryClass: EmailCampaignRepository::class)]
class EmailCampaign
{
    use TimestampableDateTimeTZTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $campaignName = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $emailSubject = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?EmailTemplate $emailTemplate = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Event $event = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?EventSession $eventSession = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?EmailCampaignStatus $emailCampaignStatus = null;

    #[ORM\Column(type: Types::DATETIMETZ_IMMUTABLE, nullable: true)]
    protected ?\DateTimeImmutable $dateSent = null;

    /**
     * @var Collection<int, EmailCampaignEventEnrollment>
     */
    #[ORM\OneToMany(targetEntity: EmailCampaignEventEnrollment::class, mappedBy: 'emailCampaign')]
    private Collection $emailCampaignEventEnrollments;

    #[ORM\Column(type: 'boolean', options: ['default' => true])]
    private bool $isActive = true;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $deletedAt = null;

    public function __construct()
    {
        $this->emailCampaignEventEnrollments = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCampaignName(): ?string
    {
        return $this->campaignName;
    }

    public function setCampaignName(string $campaignName): static
    {
        $this->campaignName = $campaignName;

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

    public function getEmailTemplate(): ?EmailTemplate
    {
        return $this->emailTemplate;
    }

    public function setEmailTemplate(?EmailTemplate $emailTemplate): static
    {
        $this->emailTemplate = $emailTemplate;

        return $this;
    }

    public function getEvent(): ?Event
    {
        return $this->event;
    }

    public function setEvent(?Event $event): static
    {
        $this->event = $event;

        return $this;
    }

    public function getEventSession(): ?EventSession
    {
        return $this->eventSession;
    }

    public function setEventSession(?EventSession $eventSession): static
    {
        $this->eventSession = $eventSession;

        return $this;
    }

    public function getEmailCampaignStatus(): ?EmailCampaignStatus
    {
        return $this->emailCampaignStatus;
    }

    public function setEmailCampaignStatus(?EmailCampaignStatus $emailCampaignStatus): static
    {
        $this->emailCampaignStatus = $emailCampaignStatus;

        return $this;
    }

    /**
     * @return Collection<int, EmailCampaignEventEnrollment>
     */
    public function getEmailCampaignEventEnrollments(): Collection
    {
        return $this->emailCampaignEventEnrollments;
    }

    public function addEmailCampaignEventEnrollment(
        EmailCampaignEventEnrollment $emailCampaignEventEnrollment,
    ): static {
        if (
            !$this->emailCampaignEventEnrollments->contains($emailCampaignEventEnrollment)
        ) {
            $this->emailCampaignEventEnrollments->add($emailCampaignEventEnrollment);
            $emailCampaignEventEnrollment->setEmailCampaign($this);
        }

        return $this;
    }

    public function removeEmailCampaignEventEnrollment(
        EmailCampaignEventEnrollment $emailCampaignEventEnrollment,
    ): static {
        if (
            $this->emailCampaignEventEnrollments->removeElement($emailCampaignEventEnrollment)
            && $emailCampaignEventEnrollment->getEmailCampaign() === $this) {
            $emailCampaignEventEnrollment->setEmailCampaign(null);
        }

        return $this;
    }

    public function getEmailSubject(): ?string
    {
        return $this->emailSubject;
    }

    public function setEmailSubject(?string $emailSubject): static
    {
        $this->emailSubject = $emailSubject;

        return $this;
    }

    public function getDateSent(): ?\DateTimeImmutable
    {
        return $this->dateSent;
    }

    public function setDateSent(?\DateTimeImmutable $dateSent): static
    {
        $this->dateSent = $dateSent;

        return $this;
    }

    public function isActive(): ?bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): static
    {
        $this->isActive = $isActive;

        return $this;
    }

    public function getDeletedAt(): ?\DateTimeImmutable
    {
        return $this->deletedAt;
    }

    public function setDeletedAt(?\DateTimeImmutable $deletedAt): static
    {
        $this->deletedAt = $deletedAt;

        return $this;
    }
}
