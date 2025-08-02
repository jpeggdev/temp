<?php

namespace App\Entity;

use App\Repository\CampaignIterationStatusRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\HasLifecycleCallbacks]
#[ORM\Entity(repositoryClass: CampaignIterationStatusRepository::class)]
#[ORM\UniqueConstraint(name: "campaign_iteration_status_name_uniq", columns: ["name"])]
class CampaignIterationStatus
{
    use Traits\TimestampableEntity;

    public const STATUS_ACTIVE = 'active';
    public const STATUS_PENDING = 'pending';
    public const STATUS_PAUSED = 'paused';
    public const STATUS_ARCHIVED = 'archived';
    public const STATUS_COMPLETED = 'completed';

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
