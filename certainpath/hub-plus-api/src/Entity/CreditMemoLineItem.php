<?php

namespace App\Entity;

use App\Entity\Trait\TimestampableDateTimeTZTrait;
use App\Entity\Trait\UuidTrait;
use App\Repository\CreditMemoLineItemRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CreditMemoLineItemRepository::class)]
#[ORM\UniqueConstraint(fields: ['uuid'])]
#[ORM\HasLifecycleCallbacks]
class CreditMemoLineItem
{
    use TimestampableDateTimeTZTrait;
    use UuidTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'creditMemoLineItems')]
    #[ORM\JoinColumn(nullable: false)]
    private ?CreditMemo $creditMemo = null;

    #[ORM\Column(length: 255)]
    private ?string $description = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $amount = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $voucherCode = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCreditMemo(): ?CreditMemo
    {
        return $this->creditMemo;
    }

    public function setCreditMemo(?CreditMemo $creditMemo): static
    {
        $this->creditMemo = $creditMemo;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getAmount(): ?string
    {
        return $this->amount;
    }

    public function setAmount(string $amount): static
    {
        $this->amount = $amount;

        return $this;
    }

    public function getVoucherCode(): ?string
    {
        return $this->voucherCode;
    }

    public function setVoucherCode(?string $voucherCode): static
    {
        $this->voucherCode = $voucherCode;

        return $this;
    }
}
