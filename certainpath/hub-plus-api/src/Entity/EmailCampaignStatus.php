<?php

namespace App\Entity;

use App\Repository\EmailCampaignStatusRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EmailCampaignStatusRepository::class)]
class EmailCampaignStatus
{
    public const string STATUS_ARCHIVED = 'archived';
    public const string STATUS_DRAFT = 'draft';
    public const string STATUS_SENDING = 'sending';
    public const string STATUS_SENT = 'sent';
    public const string STATUS_SCHEDULED = 'scheduled';
    public const string STATUS_FAILED = 'failed';

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $name = null;

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
}
