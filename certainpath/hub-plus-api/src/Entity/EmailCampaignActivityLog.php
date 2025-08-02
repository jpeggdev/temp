<?php

namespace App\Entity;

use App\Entity\Trait\TimestampableDateTimeTZTrait;
use App\Repository\EmailCampaignActivityLogRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\HasLifecycleCallbacks]
#[ORM\Entity(repositoryClass: EmailCampaignActivityLogRepository::class)]
#[ORM\UniqueConstraint(fields: ['messageId'])]
class EmailCampaignActivityLog
{
    use TimestampableDateTimeTZTrait;

    public const string EVENT_TYPE_SEND = 'send';
    public const string EVENT_TYPE_DELIVERED = 'delivered';
    public const string EVENT_TYPE_OPEN = 'open';
    public const string EVENT_TYPE_CLICK = 'click';
    public const string EVENT_TYPE_SPAM = 'spam';
    public const string EVENT_TYPE_HARD_BOUNCE = 'hard_bounce';

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $messageId = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $email = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $subject = null;

    #[ORM\Column(options: ['default' => false])]
    private ?bool $isSent = false;

    #[ORM\Column(options: ['default' => false])]
    private ?bool $isDelivered = false;

    #[ORM\Column(options: ['default' => false])]
    private ?bool $isOpened = false;

    #[ORM\Column(options: ['default' => false])]
    private ?bool $isClicked = false;

    #[ORM\Column(options: ['default' => false])]
    private ?bool $isBounced = false;

    #[ORM\Column(options: ['default' => false])]
    private ?bool $isMarkedAsSpam = false;

    #[ORM\Column(type: Types::DATETIMETZ_IMMUTABLE, nullable: true)]
    protected ?\DateTimeImmutable $eventSentAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMessageId(): ?string
    {
        return $this->messageId;
    }

    public function setMessageId(string $messageId): static
    {
        $this->messageId = $messageId;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getSubject(): ?string
    {
        return $this->subject;
    }

    public function setSubject(?string $subject): static
    {
        $this->subject = $subject;

        return $this;
    }

    public function isSent(): ?bool
    {
        return $this->isSent;
    }

    public function setIsSent(bool $isSent): static
    {
        $this->isSent = $isSent;

        return $this;
    }

    public function isDelivered(): ?bool
    {
        return $this->isDelivered;
    }

    public function setIsDelivered(bool $isDelivered): static
    {
        $this->isDelivered = $isDelivered;

        return $this;
    }

    public function isOpened(): ?bool
    {
        return $this->isOpened;
    }

    public function setIsOpened(bool $isOpened): static
    {
        $this->isOpened = $isOpened;

        return $this;
    }

    public function isClicked(): ?bool
    {
        return $this->isClicked;
    }

    public function setIsClicked(bool $isClicked): static
    {
        $this->isClicked = $isClicked;

        return $this;
    }

    public function isBounced(): ?bool
    {
        return $this->isBounced;
    }

    public function setIsBounced(bool $isBounced): static
    {
        $this->isBounced = $isBounced;

        return $this;
    }

    public function isMarkedAsSpam(): ?bool
    {
        return $this->isMarkedAsSpam;
    }

    public function setIsMarkedAsSpam(bool $isMarkedAsSpam): static
    {
        $this->isMarkedAsSpam = $isMarkedAsSpam;

        return $this;
    }

    public function getEventSentAt(): ?\DateTimeImmutable
    {
        return $this->eventSentAt;
    }

    public function setEventSentAt(\DateTimeImmutable $eventSentAt): static
    {
        $this->eventSentAt = $eventSentAt;

        return $this;
    }
}
