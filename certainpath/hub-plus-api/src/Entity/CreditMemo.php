<?php

namespace App\Entity;

use App\Entity\Trait\TimestampableDateTimeTZTrait;
use App\Entity\Trait\UuidTrait;
use App\Enum\CreditMemoStatusType;
use App\Enum\CreditMemoType;
use App\Repository\CreditMemoRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CreditMemoRepository::class)]
#[ORM\UniqueConstraint(fields: ['uuid'])]
#[ORM\HasLifecycleCallbacks]
class CreditMemo
{
    use TimestampableDateTimeTZTrait;
    use UuidTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'creditMemos')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Invoice $invoice = null;

    #[ORM\Column(type: Types::DATETIMETZ_IMMUTABLE)]
    private ?\DateTimeImmutable $cmDate = null;

    #[ORM\Column(enumType: CreditMemoStatusType::class)]
    private ?CreditMemoStatusType $status = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $totalAmount = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $reason = null;

    #[ORM\Column(enumType: CreditMemoType::class)]
    private CreditMemoType $type = CreditMemoType::VOUCHER;

    /**
     * @var Collection<int, CreditMemoLineItem>
     */
    #[ORM\OneToMany(targetEntity: CreditMemoLineItem::class, mappedBy: 'creditMemo')]
    private Collection $creditMemoLineItems;

    public function __construct()
    {
        $this->creditMemoLineItems = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getInvoice(): ?Invoice
    {
        return $this->invoice;
    }

    public function setInvoice(?Invoice $invoice): static
    {
        $this->invoice = $invoice;

        return $this;
    }

    public function getCmDate(): ?\DateTimeImmutable
    {
        return $this->cmDate;
    }

    public function setCmDate(\DateTimeImmutable $cmDate): static
    {
        $this->cmDate = $cmDate;

        return $this;
    }

    public function getStatus(): ?CreditMemoStatusType
    {
        return $this->status;
    }

    public function setStatus(?CreditMemoStatusType $status): void
    {
        $this->status = $status;
    }

    public function getTotalAmount(): ?string
    {
        return $this->totalAmount;
    }

    public function setTotalAmount(string $totalAmount): static
    {
        $this->totalAmount = $totalAmount;

        return $this;
    }

    public function getReason(): ?string
    {
        return $this->reason;
    }

    public function setReason(?string $reason): static
    {
        $this->reason = $reason;

        return $this;
    }

    public function getType(): CreditMemoType
    {
        return $this->type;
    }

    public function setType(CreditMemoType $type): void
    {
        $this->type = $type;
    }

    /**
     * @return Collection<int, CreditMemoLineItem>
     */
    public function getCreditMemoLineItems(): Collection
    {
        return $this->creditMemoLineItems;
    }

    public function addCreditMemoLineItem(CreditMemoLineItem $creditMemoLineItem): static
    {
        if (!$this->creditMemoLineItems->contains($creditMemoLineItem)) {
            $this->creditMemoLineItems->add($creditMemoLineItem);
            $creditMemoLineItem->setCreditMemo($this);
        }

        return $this;
    }

    public function removeCreditMemoLineItem(CreditMemoLineItem $creditMemoLineItem): static
    {
        if ($this->creditMemoLineItems->removeElement($creditMemoLineItem)) {
            // set the owning side to null (unless already changed)
            if ($creditMemoLineItem->getCreditMemo() === $this) {
                $creditMemoLineItem->setCreditMemo(null);
            }
        }

        return $this;
    }
}
