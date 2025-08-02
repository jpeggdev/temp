<?php

namespace App\Entity;

use App\Entity\Trait\UuidTrait;
use App\Repository\FileTmpRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

#[ORM\Entity(repositoryClass: FileTmpRepository::class)]
#[ORM\UniqueConstraint(fields: ['uuid'])]
#[ORM\HasLifecycleCallbacks]
class FileTmp
{
    use TimestampableEntity;
    use UuidTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(inversedBy: 'fileTmp', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?File $file = null;

    #[ORM\Column(nullable: false, options: ['default' => 'false'])]
    private ?bool $isCommited = false;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFile(): ?File
    {
        return $this->file;
    }

    public function setFile(File $file): static
    {
        $this->file = $file;

        return $this;
    }

    public function isCommited(): ?bool
    {
        return $this->isCommited;
    }

    public function setIsCommited(bool $isCommited): static
    {
        $this->isCommited = $isCommited;

        return $this;
    }
}
