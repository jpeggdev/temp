<?php

namespace App\Entity;

use App\Entity\Traits\TimestampableEntity;
use App\Repository\BatchStatusRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\HasLifecycleCallbacks]
#[ORM\Entity(repositoryClass: BatchStatusRepository::class)]
#[ORM\UniqueConstraint(name: "batch_status_name_uniq", columns: ["name"])]
class BatchStatus
{
    use TimestampableEntity;

    public const STATUS_NEW = 'new';
    public const STATUS_SENT = 'sent';
    public const STATUS_PROCESSED = 'processed';
    public const STATUS_INVOICED = 'invoiced';
    public const STATUS_PAUSED = 'paused';
    public const STATUS_ARCHIVED = 'archived';
    public const STATUS_COMPLETE = 'complete';

    public const STATUS_UPLOADED = 'uploaded';

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
