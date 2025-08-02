<?php

namespace App\Entity;

use App\Repository\EventEmployeeRoleMappingRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EventEmployeeRoleMappingRepository::class)]
class EventEmployeeRoleMapping
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'eventEmployeeRoleMappings')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Event $event = null;

    #[ORM\ManyToOne(inversedBy: 'eventEmployeeRoleMappings')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?EmployeeRole $employeeRole = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getEmployeeRole(): ?EmployeeRole
    {
        return $this->employeeRole;
    }

    public function setEmployeeRole(?EmployeeRole $employeeRole): static
    {
        $this->employeeRole = $employeeRole;

        return $this;
    }
}
