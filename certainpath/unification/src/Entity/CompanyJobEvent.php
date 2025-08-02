<?php

namespace App\Entity;

use App\Repository\CompanyJobEventRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CompanyJobEventRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ORM\Index(name: 'company_job_event_job_name_idx', columns: ['job_name'])]
#[ORM\Index(name: 'company_job_event_created_at_idx', columns: ['created_at'])]
class CompanyJobEvent
{
    use Traits\TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: EventStatus::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?EventStatus $eventStatus = null;

    #[ORM\Column(type: Types::TEXT)]
    private string $jobName;

    #[ORM\ManyToOne(targetEntity: Company::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?Company $company = null;

    public function setEventStatus(EventStatus $eventStatus): void
    {
        $this->eventStatus = $eventStatus;
    }

    public function getEventStatus(): EventStatus
    {
        return $this->eventStatus;
    }

    public function setJobName(string $identifier): void
    {
        $this->jobName = $identifier;
    }

    public function getJobName(): string
    {
        return $this->jobName;
    }

    public function setCompany(Company $company): void
    {
        $this->company = $company;
    }

    public function getCompany(): ?Company
    {
        return $this->company;
    }
}
