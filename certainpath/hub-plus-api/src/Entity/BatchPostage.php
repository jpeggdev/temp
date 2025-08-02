<?php

namespace App\Entity;

use App\Module\Stochastic\Feature\PostageUploads\Repository\BatchPostageRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BatchPostageRepository::class)]
#[ORM\HasLifecycleCallbacks]
class BatchPostage
{
    use Trait\TimestampableDateTimeTZTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $reference = null;

    #[ORM\Column(options: ['default' => 0])]
    private int $quantitySent = 0;

    #[ORM\Column(type: Types::DECIMAL, precision: 12, scale: 2)]
    private string $cost = '0.00';

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getReference(): ?string
    {
        return $this->reference;
    }

    public function setReference(string $reference): static
    {
        $this->reference = $reference;

        return $this;
    }

    public function getQuantitySent(): int
    {
        return $this->quantitySent;
    }

    public function setQuantitySent(int $quantitySent): static
    {
        $this->quantitySent = $quantitySent;

        return $this;
    }

    public function getCost(): string
    {
        return $this->cost;
    }

    public function setCost(string $cost): static
    {
        $this->cost = $cost;

        return $this;
    }
}
