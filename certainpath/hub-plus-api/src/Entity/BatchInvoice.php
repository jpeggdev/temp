<?php

namespace App\Entity;

use App\Repository\BatchInvoiceRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BatchInvoiceRepository::class)]
class BatchInvoice
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $accountIdentifier = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $batchReference = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $invoiceReference = null;

    #[ORM\Column(type: 'json')]
    private ?string $data = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBatchReference(): ?string
    {
        return $this->batchReference;
    }

    public function setBatchReference(string $batchReference): static
    {
        $this->batchReference = $batchReference;

        return $this;
    }

    public function getInvoiceReference(): ?string
    {
        return $this->invoiceReference;
    }

    public function setInvoiceReference(string $invoiceReference): static
    {
        $this->invoiceReference = $invoiceReference;

        return $this;
    }

    public function getData(): string
    {
        return $this->data;
    }

    public function setData(string $data): static
    {
        $this->data = $data;

        return $this;
    }

    public function getAccountIdentifier(): ?string
    {
        return $this->accountIdentifier;
    }

    public function setAccountIdentifier(string $accountIdentifier): static
    {
        $this->accountIdentifier = $accountIdentifier;

        return $this;
    }

    public function isInvoiced(): bool
    {
        return
            $this->getId()
            && $this->getBatchReference()
            && $this->getInvoiceReference()
        ;
    }
}
