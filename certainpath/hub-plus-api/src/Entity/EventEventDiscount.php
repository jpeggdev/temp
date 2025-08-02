<?php

namespace App\Entity;

use App\Repository\EventEventDiscountRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EventEventDiscountRepository::class)]
class EventEventDiscount
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'eventEventDiscounts')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Event $event = null;

    #[ORM\ManyToOne(inversedBy: 'eventEventDiscounts')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?EventDiscount $eventDiscount = null;

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

    public function getEventDiscount(): ?EventDiscount
    {
        return $this->eventDiscount;
    }

    public function setEventDiscount(?EventDiscount $eventDiscount): static
    {
        $this->eventDiscount = $eventDiscount;

        return $this;
    }
}
