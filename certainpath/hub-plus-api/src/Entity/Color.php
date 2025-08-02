<?php

namespace App\Entity;

use App\Repository\ColorRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ColorRepository::class)]
#[ORM\UniqueConstraint(name: 'unique_color_value', columns: ['value'])]
class Color
{
    public const string COLOR_BLUE = '#3B82F6';
    public const string COLOR_RED = '#EF4444';
    public const string COLOR_GREEN = '#10B981';
    public const string COLOR_ORANGE = '#F59E0B';
    public const string COLOR_PURPLE = '#8B5CF6';
    public const string COLOR_PINK = '#EC4899';
    public const string COLOR_GRAY = '#6B7280';

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $value = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function setValue(string $value): static
    {
        $this->value = $value;

        return $this;
    }
}
