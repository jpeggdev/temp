<?php

namespace App\Entity;

use App\Repository\AddressRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AddressRepository::class)]
#[ORM\Index(name: 'address_address_external_id_idx', columns: ['external_id'])]
#[ORM\Index(name: 'address_company_address_external_id_idx', columns: ['company_id', 'external_id'])]
#[ORM\HasLifecycleCallbacks]
class Address extends AbstractAddress
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => false])]
    private ?bool $isDoNotMail = false;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => false])]
    private ?bool $isGlobalDoNotMail = false;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Company $company = null;

    /**
     * @var Collection<int, Prospect>
     */
    #[ORM\ManyToMany(targetEntity: Prospect::class, mappedBy: 'addresses')]
    private Collection $prospects;

    /**
     * @var Collection<int, Prospect>
     */
    #[ORM\ManyToMany(targetEntity: Customer::class, mappedBy: 'addresses')]
    private Collection $customers;

    public function __construct()
    {
        $this->prospects = new ArrayCollection();
        $this->customers = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function isDoNotMail(): ?bool
    {
        if ($this->isGlobalDoNotMail()) {
            $this->setDoNotMail(true);
        }
        return $this->isDoNotMail;
    }

    public function setDoNotMail(bool $isDoNotMail): static
    {
        $this->isDoNotMail = $isDoNotMail;

        return $this;
    }

    public function isGlobalDoNotMail(): ?bool
    {
        return $this->isGlobalDoNotMail;
    }

    public function setGlobalDoNotMail(bool $isGlobalDoNotMail): static
    {
        $this->isGlobalDoNotMail = $isGlobalDoNotMail;

        return $this;
    }

    public function getCompany(): ?Company
    {
        return $this->company;
    }

    public function setCompany(?Company $company): static
    {
        $this->company = $company;
        return $this;
    }

    /**
     * @return Collection<int, Prospect>
     */
    public function getProspects(): Collection
    {
        return $this->prospects;
    }

    public function addProspect(Prospect $prospect): static
    {
        if (!$this->prospects->contains($prospect)) {
            $this->prospects->add($prospect);
        }

        return $this;
    }

    public function removeProspect(Prospect $prospect): static
    {
        $this->prospects->removeElement($prospect);

        return $this;
    }

    /**
     * @return Collection<int, Customer>
     */
    public function getCustomers(): Collection
    {
        return $this->customers;
    }

    public function addCustomer(Customer $customer): static
    {
        if (!$this->customers->contains($customer)) {
            $this->customers->add($customer);
        }

        return $this;
    }

    public function removeCustomer(Customer $customer): static
    {
        $this->customers->removeElement($customer);

        return $this;
    }

    public function isPoBox(): bool
    {
        $address1 = $this->getAddress1();
        if (!$address1) {
            return false;
        }
        $match = preg_match('/^p\.? *o\.? *box/i', $this->getAddress1());
        return ($match !== false) && ($match > 0);
    }

    public function getAsArray(): array
    {
        return [
            $this->getAddress1(),
            $this->getAddress2(),
            $this->getCity(),
            $this->getPostalCode(),
            $this->getStateCode(),
        ];
    }

    public function getAsString(): string
    {
        return implode(' ', $this->getAsArray());
    }
}
