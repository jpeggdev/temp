<?php

namespace App\Entity;

use App\Repository\EmailTemplateVariableRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

#[ORM\Entity(repositoryClass: EmailTemplateVariableRepository::class)]
#[ORM\HasLifecycleCallbacks]
class EmailTemplateVariable
{
    use TimestampableEntity;

    public const string SESSION_NAME = '*|SessionName|*';
    public const string SESSION_START_DATE = '*|StartDate|*';
    public const string SESSION_END_DATE = '*|EndDate|*';
    public const string SESSION_START_TIME = '*|StartTime|*';
    public const string SESSION_END_TIME = '*|EndTime|*';
    public const string SESSION_TIME_ZONE = '*|TimeZone|*';
    public const string EVENT_DESCRIPTION = '*|EventDescription|*';
    public const string EVENT_IMAGE_URL = '*|EventImageURL|*';
    public const string EVENT_TYPE = '*|EventType|*';
    public const string EVENT_CATEGORY = '*|EventCategory|*';
    public const string EVENT_VIRTUAL_LINK = '*|EventVirtualLink|*';

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

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
}
