<?php

namespace App\Entity;

use App\Entity\Traits\TimestampableEntity;
use App\Repository\CampaignEventRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CampaignEventRepository::class)]
#[ORM\Index(name: 'campaign_event_campaign_identifier_idx', columns: ['campaign_identifier'])]
#[ORM\Index(name: 'company_event_created_at_idx', columns: ['created_at'])]
#[ORM\HasLifecycleCallbacks]
class CampaignEvent
{
    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private EventStatus $eventStatus;

    #[ORM\ManyToOne(inversedBy: 'campaignEvents')]
    private ?Campaign $campaign = null;

    #[ORM\Column(type: Types::TEXT, nullable: false)]
    private ?string $campaignIdentifier;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $errorMessage = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEventStatus(): EventStatus
    {
        return $this->eventStatus;
    }

    public function setEventStatus(EventStatus $eventStatus): static
    {
        $this->eventStatus = $eventStatus;

        return $this;
    }

    public function getCampaignIdentifier(): string
    {
        return $this->campaignIdentifier;
    }

    public function setCampaignIdentifier(string $campaignIdentifier): static
    {
        $this->campaignIdentifier = $campaignIdentifier;

        return $this;
    }

    public function getErrorMessage(): ?string
    {
        return $this->errorMessage;
    }

    public function setErrorMessage(?string $errorMessage): static
    {
        $this->errorMessage = $errorMessage;

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
}
