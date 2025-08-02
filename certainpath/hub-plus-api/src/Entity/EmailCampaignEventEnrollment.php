<?php

namespace App\Entity;

use App\Repository\EmailCampaignEventEnrollmentRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EmailCampaignEventEnrollmentRepository::class)]
class EmailCampaignEventEnrollment
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'emailCampaignEventEnrollments')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?EmailCampaign $emailCampaign = null;

    #[ORM\ManyToOne(inversedBy: 'emailCampaignEventEnrollments')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?EventEnrollment $eventEnrollment = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmailCampaign(): ?EmailCampaign
    {
        return $this->emailCampaign;
    }

    public function setEmailCampaign(?EmailCampaign $emailCampaign): static
    {
        $this->emailCampaign = $emailCampaign;

        return $this;
    }

    public function getEventEnrollment(): ?EventEnrollment
    {
        return $this->eventEnrollment;
    }

    public function setEventEnrollment(?EventEnrollment $eventEnrollment): static
    {
        $this->eventEnrollment = $eventEnrollment;

        return $this;
    }
}
